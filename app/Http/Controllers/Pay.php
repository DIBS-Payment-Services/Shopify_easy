<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\ShopifyApiService;
use App\MerchantSettings;
use App\Service\EasyService;
use App\Service\EasyApiService;
use App\PaymentDetails;
use App\CheckoutObject;

class Pay extends Controller
{
    protected $shopify_access_token;
    protected $shop_url;
    private $shopifyAppService;
    private $request;
    private $easyService;
    private $easyApiService;
    private $checkoutObject;
    private $logger;
    private $easyApiExceptionHandler;
    private $shopifyApiExceptionHandler;
    
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

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        try {
            return $this->startPayment($request);
        }catch(\App\Exceptions\EasyException $e) {
           $this->easyApiExceptionHandler->handle($e, $request->all());
        }catch(\App\Exceptions\ShopifyApiException $e ) {
           $this->shopifyApiExceptionHandler->handle($e);
        }
        catch(\Exception $e) {
           $this->logger->error($e->getMessage());
       }
    }

    protected function startPayment(Request $request) {
      $settingsCollection = MerchantSettings::getSettingsByShopName(urlencode($request->get('x_shop_name')));
      $accessToken = $settingsCollection->first()->access_token;
      $shopUrl = $settingsCollection->first()->shop_url;
      $checkout = $this->shopifyAppService->getCheckoutById($accessToken, $shopUrl, $request->get('x_reference'));
      $this->checkoutObject->setCheckout($checkout);
      if(empty($checkout)) {
          throw new \Exception('Checkout with id: '. $request->get('x_reference') .' not found');
      }
      
      if($request->get('x_test') == 'true') {
        $key = ShopifyApiService::decryptKey($settingsCollection->first()->easy_test_secret_key);
        $env = EasyApiService::ENV_TEST;
      } else {
        $key = ShopifyApiService::decryptKey($settingsCollection->first()->easy_secret_key);
        $env = EasyApiService::ENV_LIVE;
      }
      $settings = current($settingsCollection->toArray());
      $data = json_encode($this->easyService->generateRequestParams($settings, $this->checkoutObject));
      $this->easyApiService->setAuthorizationKey($key);
      $this->easyApiService->setEnv($env);
      
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
                      'test' => $request->get('x_test') == 'true' ? 1 : 0];
           
           PaymentDetails::addOrUpdateDetails($params);
           session(['request_params' => json_encode($requestParams)]);
           $redirectUrl = $result_decoded->hostedPaymentPageUrl . '&language=' . $settingsCollection->first()->language;
           return redirect($redirectUrl);
      }
   } 
}
