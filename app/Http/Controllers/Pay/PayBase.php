<?php

namespace App\Http\Controllers\Pay;

use App\Service\ShopifyApiService;
use App\MerchantSettings;
use App\Service\EasyService;
use App\Service\EasyApiService;
use App\PaymentDetails;
use App\CheckoutObject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Description of PayBase
 *
 * @author mabe
 */
class PayBase extends \App\Http\Controllers\Controller {

    protected $shopify_access_token;
    protected $shop_url;
    protected $shopifyAppService;
    protected $request;
    protected $easyService;
    protected $easyApiService;
    protected $checkoutObject;
    protected $logger;
    protected $easyApiExceptionHandler;
    protected $shopifyApiExceptionHandler;

    public function __construct(ShopifyApiService $service,
                                Request $request, EasyService $easyService,
            EasyApiService $easyApiService, CheckoutObject $checkoutObject, 
            \Illuminate\Log\Logger $logger, \App\Exceptions\EasyApiExceptionHandler $eh,
            \App\Exceptions\ShopifyApiExceptionHandler $ehsh
            ) {
        $this->shopifyAppService = $service;
        $this->request = $request;
        $this->easyService = $easyService;
        $this->easyApiService = $easyApiService;
        $this->checkoutObject = $checkoutObject;
        $this->logger=  $logger;
        $this->easyApiExceptionHandler = $eh;
        $this->shopifyApiExceptionHandler = $ehsh;
        
    }

   protected function startPayment(Request $request) {
      $settingsCollection = MerchantSettings::getSettingsByMerchantId($request->get('x_account_id'));
      if($settingsCollection->count() > 1) {
          $settingsCollection = MerchantSettings::getSettingsByShopName($request->get('x_shop_name'));
      }
      if($settingsCollection->count() == 0) {
          throw new \App\Exceptions\ShopifyApiException('Cant identyfy the shop. Please check app settings'. PHP_EOL .
                                                        'Merchantid: ' . $request->get('x_account_id') .
                                                        'Shop name:' . $request->get('x_shop_name'));
      }
      $settings = $settingsCollection->first()->toArray();
      $reference = $request->get('x_reference');
      $checkout = $this->shopifyAppService->getCheckoutById($settings['access_token'], $settings['shop_url'], $reference);

      if(empty($checkout)) {
          sleep(30);
          $checkout = $this->shopifyAppService->getCheckoutById($settings['access_token'], $settings['shop_url'], $reference);
      }

      // if checkout still empty, generate fake checkout
      if(empty($checkout)) {
         $checkout = $this->easyService->getFakeChekout($request);
      }

      $key = ShopifyApiService::decryptKey($settings[static::KEY]);
      $this->easyApiService->setAuthorizationKey($key);
      $this->easyApiService->setEnv(static::ENV);
      $this->checkoutObject->setCheckout($checkout);
      $createPaymentParams = $this->easyService->generateRequestParams($settings, $this->checkoutObject);

      $result = $this->easyApiService->createPayment(json_encode($createPaymentParams));
      if($result->getHttpStatus() == 400) {
          $errorObject = json_decode($result->getResponse(), true);
          foreach ($errorObject['errors'] as $key=>$value) {
              // if postal code is not valid try to create checkout withput postal code
              if('checkout.Consumer.ShippingAddress.PostalCode' == $key) {
                  unset($createPaymentParams['checkout']['merchantHandlesConsumerData']);
              }
          }
          $result = $this->easyApiService->createPayment(json_encode($createPaymentParams));
      }
      if(!$result->isSuccess()) {
          $errorMessage = $result->getHttpStatus()? $result->getResponse() : $result->getErrorMessage();
          throw new \App\Exceptions\EasyException($errorMessage, $result->getHttpStatus());
      }
      $createPaymentResult = json_decode($result->getResponse());
      $requestParams = $request->all();
      $requestParams['paymentId'] = $createPaymentResult->paymentId;
      $requestParams['gateway_password'] = $settings['gateway_password'];
      $requestParams['shop'] = $settings['shop_url'];
      $requestParams['checkout_token'] = $checkout['token'];
      $paramsToSave = ['checkout_id' => $checkout['id'],
                 'dibs_paymentid' => $createPaymentResult->paymentId, 
                 'shop_url' => $settings['shop_url'],
                 'test' => static::ENV == 'test' ? 1 : 0,
                 'amount' => $request->get('x_amount'),
                 'currency' => $request->get('x_currency'),
                 'create_payment_items_params' => 
                 json_encode($createPaymentParams['order']['items'])];
      DB::enableQueryLog();
      $this->logger->debug('START add or update Details query................');
      PaymentDetails::addOrUpdateDetails($paramsToSave);
      $this->logger->debug(DB::getQueryLog());
      $this->logger->debug('last inserted id = ' .  DB::getPdo()->lastInsertId());
      $this->logger->debug('END update Details query................');
      return redirect($createPaymentResult->hostedPaymentPageUrl . '&' . http_build_query(['language' => $settings['language']]));
   }

   protected function showErrorPage($message) {
       return view('easy-pay-error', ['message' => $message, 
                   'back_link' => $this->request->get('x_url_complete')]);
   }

}
