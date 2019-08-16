<?php

namespace App\Http\Controllers;

use App\Service\ShopifyApiService;
use App\MerchantSettings;
use App\Service\EasyService;
use App\Service\EasyApiService;
use App\PaymentDetails;
use App\CheckoutObject;
use Illuminate\Http\Request;

/**
 * Description of PayBase
 *
 * @author mabe
 */
class PayBase extends Controller {

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

      $accessToken = $settingsCollection->first()->access_token;
      $shopUrl = $settingsCollection->first()->shop_url;
      $params = $request->all();

      unset($params['x_signature']);
      $calculatedSignature = $this->shopifyAppService->calculateSignature($params, trim($settingsCollection->first()->gateway_password));
      /*if($request->get('x_signature') != $calculatedSignature) {
          throw new \App\Exceptions\ShopifyApiException('Signature not match while trying to pay');
      }
      */
      $checkout = $this->shopifyAppService->getCheckoutById($accessToken, $shopUrl, $request->get('x_reference'));

      if(empty($checkout)) {
          throw new \App\Exceptions\ShopifyApiException('Checkout with id: '. $request->get('x_reference') .' not found');
      }

      $settings = current($settingsCollection->toArray());
      $this->checkoutObject->setCheckout($checkout);
      $createPaymentParams = $this->easyService->generateRequestParams($settings, $this->checkoutObject);
      $data = json_encode($createPaymentParams);

      $filedName = static::KEY;
      $key = ShopifyApiService::decryptKey($settingsCollection->first()->$filedName);
      $this->easyApiService->setAuthorizationKey($key);
      $this->easyApiService->setEnv(static::ENV);

      if($result = $this->easyApiService->createPayment($data)) {
           $result_decoded = json_decode($result);
           $requestParams = $request->all();
           $requestParams['gateway_password'] = $settingsCollection->first()->gateway_password;
           $requestParams['shop'] = $settingsCollection->first()->shop_url;
           $requestParams['paymentId'] = $result_decoded->paymentId;
           $requestParams['checkout_token'] = $checkout['token'];
           $params = ['checkout_id' => $checkout['id'],
                      'dibs_paymentid' => $result_decoded->paymentId, 
                      'shop_url' => $settingsCollection->first()->shop_url,
                      'test' => static::ENV == 'test' ? 1 : 0];
           $params['create_payment_items_params'] = json_encode($createPaymentParams['order']['items']);
           PaymentDetails::addOrUpdateDetails($params);
           session(['request_params' => json_encode($requestParams)]);
           $redirectUrl = $result_decoded->hostedPaymentPageUrl . '&language=' . $settingsCollection->first()->language;
           return redirect($redirectUrl);
      }
   }

   protected function showErrorPage($message) {
       return view('easy-pay-error', ['message' => $message, 
                   'back_link' => $this->request->get('x_url_complete')]);
   }

}
