<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\ShopifyApiService;
use App\PaymentDetails;
use App\MerchantSettings;
use App\Service\EasyApiService;

/**
 * Description of OrderCreated
 *
 * @author mabe
 */
class OrderCreatedHook extends Controller{
    
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(EasyApiService $easyApiService, 
                             Request $request, 
                             \App\Exceptions\EasyApiExceptionHandler $eh,
                             \App\Exceptions\Handler $handler)
    {
        try{
            $collectionPaymentDetail = PaymentDetails::getDetailsByCheckouId($request->get('checkout_id'));
            $settingsCollection = MerchantSettings::getSettingsByShopUrl($collectionPaymentDetail->first()->shop_url);
            $paymentId = $collectionPaymentDetail->first()->dibs_paymentid;
            if($collectionPaymentDetail->first()->test == 1) {
                $key = ShopifyApiService::decryptKey($settingsCollection->first()->easy_test_secret_key);
                $easyApiService->setEnv(EasyApiService::ENV_TEST);
            }else {
                $key = ShopifyApiService::decryptKey($settingsCollection->first()->easy_secret_key);
                $easyApiService->setEnv(EasyApiService::ENV_LIVE);
            }
            $easyApiService->setAuthorizationKey($key);
            $paymentJson = $easyApiService->getPayment($paymentId);
            $paymentObj = json_decode($paymentJson);
            $jsonData = json_encode(['reference' => $request->get('name'), 'checkoutUrl' => $paymentObj->payment->checkout->url]);
            $easyApiService->updateReference($paymentId, $jsonData);
   
        } catch(\App\Exceptions\EasyException $e ) {
           $eh->handle($e);
           return response('HTTP/1.0 500 Internal Server Error', 500);
        } 
        catch(\Exception $e) {
           $handler->report($e);
           return response('HTTP/1.0 500 Internal Server Error', 500);
        }
    }
}
