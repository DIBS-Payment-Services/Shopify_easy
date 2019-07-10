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
    public function __invoke(EasyApiService $easyApiService, Request $request)
    {
        try{
        $collectionPaymentDetail = PaymentDetails::getDetailsByCheckouId($request->get('checkout_id'));
        $settingsCollection = MerchantSettings::getSettingsByShopUrl($collectionPaymentDetail->first()->shop_url);
        $paymentId = $collectionPaymentDetail->first()->dibs_paymentid;
        if($collectionPaymentDetail->first()->test == 1) {
            $urlUpdateReference = 'https://test.api.dibspayment.eu/v1/payments/'.  $paymentId .'/referenceinformation';
            $key = ShopifyApiService::decryptKey($settingsCollection->first()->easy_test_secret_key);
            $url = ShopifyApiService::GET_PAYMENT_DETAILS_URL_TEST_PREFIX . $paymentId;
        }else {
            $urlUpdateReference = 'https://api.dibspayment.eu/v1/payments/'. $paymentId .'/referenceinformation';
            $key = ShopifyApiService::decryptKey($settingsCollection->first()->easy_test_secret_key);
            $url = ShopifyApiService::GET_PAYMENT_DETAILS_URL_TEST_PREFIX . $paymentId;
        }
        $easyApiService->setAuthorizationKey($key);
        $paymentJson = $easyApiService->getPayment($url);
        $paymentObj = json_decode($paymentJson);
        $jsonData = json_encode(['reference' => $request->get('name'), 'checkoutUrl' => $paymentObj->payment->checkout->url]);
        $easyApiService->updateReference($urlUpdateReference, $jsonData);
           header("HTTP/1.1 200 OK");
        } catch( \Exception $e ) {
           header("HTTP/1.1 500 Callback filed");
        }
    }
}
