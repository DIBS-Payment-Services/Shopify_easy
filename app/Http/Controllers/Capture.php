<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\ShopifyApiService;
use App\MerchantSettings;
use App\Service\EasyService;
use App\Service\EasyApiService;
use App\CheckoutObject;

class Capture extends Controller
{
   
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, 
                             ShopifyApiService $shopifyApiService, 
                             EasyApiService $easyApiService, 
                             CheckoutObject $checkoutObject, 
                             EasyService $easyService,
                             \App\ShopifyReturnParams $shopifyReturnParams, 
                             \App\Exceptions\ShopifyApiExceptionHandler $ehsh,
                             \App\Exceptions\EasyApiExceptionHandler $eh,
                             \App\Exceptions\Handler $handler)
    {
        try{
             $settingsCollection = MerchantSettings::getSettingsByMerchantId($request->get('x_account_id'));
             $gatewayPassword = $settingsCollection->first()->gateway_password;
             $params = $request->all();
             $secretKey = null;

             if($request->get('x_test')) {
                    $params['x_test'] = 'true';
                    $secretKey = ShopifyApiService::decryptKey($settingsCollection->first()->easy_test_secret_key);
                    $easyApiService->setEnv(EasyApiService::ENV_TEST);
             } else {
                    $params['x_test'] = 'false';
                    $secretKey = ShopifyApiService::decryptKey($settingsCollection->first()->easy_secret_key);
                    $easyApiService->setEnv(EasyApiService::ENV_LIVE);
             }
             unset($params['x_signature']);
             
             if($request->get('x_signature') != $shopifyApiService->calculateSignature($params, $gatewayPassword)) {
                throw new \App\Exceptions\ShopifyApiException('Singnature is wrong while trying to capture');
             }
            
             $orderJson = $shopifyApiService->getOrder($settingsCollection->first()->access_token, 
             $settingsCollection->first()->shop_url, $request->get('x_shopify_order_id'));
             $orderDecoded = json_decode($orderJson, true);
             
             $checkoutObject->setCheckout($orderDecoded['order']);
             
             if(($request->get('x_amount') * 100) != $checkoutObject->getAmount()) {
                 throw new \App\Exceptions\ShopifyApiException('Only full amount capture is allowed');
             }
          
             $data['amount'] = $checkoutObject->getAmount();
             $data['orderItems'] = $easyService->getRequestObjectItems($checkoutObject);
             
             $easyApiService->setAuthorizationKey($secretKey);
             $easyApiService->chargePayment($request->get('x_gateway_reference'), json_encode($data));
             $shopifyReturnParams->setX_GatewayReference($request->get('x_gateway_reference'));
             $shopifyReturnParams->setX_Reference($request->get('x_reference'));
             $shopifyReturnParams->setX_Result('completed');
             $shopifyReturnParams->setX_Timestamp(date("Y-m-d\TH:i:s\Z"));
             $shopifyReturnParams->setX_TransactionType('capture');
             $shopifyReturnParams->setX_Message();
             $pass = $settingsCollection->first()->gateway_password;
             $shopifyReturnParams->setX_Signature($shopifyApiService->calculateSignature($shopifyReturnParams->getParams(),$pass));
             
             // wait 30 seconds untill capture processed in Shopify 
             $this->flushHeader();
             sleep(30);
    
             $shopifyApiService->paymentCallback($request->get('x_url_callback'), $shopifyReturnParams->getParams());
             
         } catch(\App\Exceptions\ShopifyApiException $e) {
            $ehsh->handle($e, $request->all());
            return response('HTTP/1.0 500 Internal Server Error', 500);
         } catch(\App\Exceptions\EasyException $e) {
            $eh->handle($e);
            return response('HTTP/1.0 500 Internal Server Error', 500);
         } 
         catch(\Exception $e) {
            $handler->report($e);
            return response('HTTP/1.0 500 Internal Server Error', 500);
         }
    }

    protected function flushHeader() {
        ob_start();
        echo "OK";
        $size = ob_get_length();
        header("Content-Encoding: none");
        header("Content-Length: {$size}");
        header("Connection: close");
        if( ob_get_level() > 0 ) ob_flush();
        ob_end_flush();
        flush();
   }
}
