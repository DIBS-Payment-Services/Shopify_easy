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
                             \App\Exceptions\Handler $handler,
                             \Illuminate\Log\Logger $logger)
    {
        if(!strstr($request->get('gateway'), 'dibs_easy_checkout')) {
            return response('HTTP/1.0 500 Internal Server Error', 200);
        }
        try{
            $collectionPaymentDetail = PaymentDetails::getDetailsByCheckouId($request->get('checkout_id'));
            if( $collectionPaymentDetail->count() == 0) {
                return response('OK', 200);
            }
            $settingsCollection = MerchantSettings::getSettingsByShopOrigin($collectionPaymentDetail->first()->shop_url);
            $paymentId = $collectionPaymentDetail->first()->dibs_paymentid;
            if($collectionPaymentDetail->first()->test == 1) {
                $key = ShopifyApiService::decryptKey($settingsCollection->first()->easy_test_secret_key);
                $easyApiService->setEnv(EasyApiService::ENV_TEST);
            }else {
                $key = ShopifyApiService::decryptKey($settingsCollection->first()->easy_secret_key);
                $easyApiService->setEnv(EasyApiService::ENV_LIVE);
            }
            $easyApiService->setAuthorizationKey($key);
            $payment = $easyApiService->getPayment($paymentId);
            $jsonData = json_encode(['reference' => $request->get('name'), 
                                     'checkoutUrl' => $payment->getCheckoutUrl()]);
            $easyApiService->updateReference($paymentId, $jsonData);
        } catch(\App\Exceptions\EasyException $e ) {
           $eh->handle($e);
           return response('HTTP/1.0 500 Internal Server Error', 500);
        }
        catch(\Exception $e) {
           $handler->report($e);
           $logger->debug($request->all());
           return response('HTTP/1.0 500 Internal Server Error', 500);
        }
    }
}
