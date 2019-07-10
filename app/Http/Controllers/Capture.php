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
    public function __invoke(Request $request, ShopifyApiService $shopifyApiService, 
                            EasyApiService $easyApiService, CheckoutObject $checkoutObject, EasyService $easyService)
    {
        try{
        $settingsCollection = MerchantSettings::getSettingsByMerchantId($request->get('x_account_id'));
         $gatewayPassword = $settingsCollection->first()->gateway_password;
         $params = $request->all();
         $secretKey = null;
         if(!empty($params['x_test'])) {
                $secretKey = ShopifyApiService::decryptKey($settingsCollection->first()->easy_test_secret_key);
                $params['x_test'] = 'true';
                $url = EasyApiService::CHARGE_PAYMENT_URL_TEST_PREFIX . $request->get('x_gateway_reference') . '/charges';
         } else {
                $secretKey = ShopifyApiService::decryptKey($settingsCollection->first()->easy_secret_key);
                $url = EasyApiService::CHARGE_PAYMENT_URL_PREFIX . $request->get('x_gateway_reference') . '/charges';
         }
     
         unset($params['x_signature']);
         $signature = $shopifyApiService->calculateSignature($params, $gatewayPassword);
        
         if($request->get('x_signature') == $signature) {
             $orderJson = $shopifyApiService->getOrder($settingsCollection->first()->access_token, 
                                $settingsCollection->first()->shop_url, $request->get('x_shopify_order_id'));
             $orderDecoded = json_decode($orderJson, true);
             $checkoutObject->setCheckout($orderDecoded['order']);
            
             $data = [];
             if($request->get('x_amount') * 100  ==  $checkoutObject->getAmount()) {
                $data['amount'] = $checkoutObject->getAmount();
                $data['orderItems'] = $easyService->getRequestObjectItems($checkoutObject);;
                $easyApiService->setAuthorizationKey($secretKey);
                $easyApiService->chargePayment($url, json_encode($data));
                
             } else {
                $data['amount'] = $request->get('x_amount') * 100;
                $easyApiService->setAuthorizationKey($secretKey);
                $easyApiService->chargePayment($url, json_encode($data));
                $data['orderItems'] =  $easyService->getFakeOrderRow($request->get('x_amount') * 100);
                $easyApiService->setAuthorizationKey($secretKey);
                $easyApiService->chargePayment($url, json_encode($data));
             }
         } 
         $params = [
                  'x_gateway_reference' => $request->get('x_gateway_reference'),
                  'x_reference'         => $request->get('x_reference'),
                  'x_result'            => 'completed',
                  'x_timestamp'         => date("Y-m-d\TH:i:s\Z"),
                  'x_transaction_type'  => 'capture',
                  'x_message' => ''];
         $params['x_signature'] = $shopifyApiService->calculateSignature($params, $settingsCollection->first()->gateway_password);
         $url = $request->get('x_url_callback');
         $this->flushHeader();
         sleep(30);
         $shopifyApiService->paymentCallback($url, $params);
            header("HTTP/1.1 200 OK");
        } catch(App\Exceptions\EasyException $e) {
            header("HTTP/1.1 500 Callback filed");
        }
    }

    protected function flushHeader() {
        ob_start();
        echo "OK";
        $size = ob_get_length();
        header("Content-Encoding: none");
        header("Content-Length: {$size}");
        header("Connection: close");
        ob_end_flush();
        ob_flush();
        flush();
   }
}
