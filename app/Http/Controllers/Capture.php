<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\ShopifyApiService;
use App\MerchantSettings;
use App\Service\EasyService;
use App\Service\EasyApiService;
use App\CheckoutObject;
use App\PaymentDetails;

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
             $paymentDetails = PaymentDetails::getDetailsByPaymentId($request->get('x_gateway_reference'));
             $settingsCollection = MerchantSettings::getSettingsByShopUrl($paymentDetails->first()->shop_url);
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
             PaymentDetails::setCaptureRequestParams($orderDecoded['order']['checkout_id'], json_encode($request->all()));
             if(($request->get('x_amount') * 100) == $checkoutObject->getAmount()) {
                 $data['amount'] = $checkoutObject->getAmount();
                 $data['orderItems'] = json_decode($paymentDetails->first()->create_payment_items_params, true);
             } else {
                 $data['amount'] = $request->get('x_amount') * 100;
                 $data['orderItems'][] = $easyService->getFakeOrderRow($request->get('x_amount'), 'captured-partially1');
             }
             $paramsRequestJson = json_encode($request->all());
             $easyApiService->setAuthorizationKey($secretKey);
             $easyApiService->chargePayment($request->get('x_gateway_reference'), json_encode($data));
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
}
