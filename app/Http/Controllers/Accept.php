<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\ShopifyApiService;
use App\MerchantSettings;
use App\Service\EasyApiService;

use App\PaymentDetails;

class Accept extends Controller
{
    protected $easyApiService;
    protected $shopifyApiService;
    
    public function __construct(EasyApiService $easyApiService, ShopifyApiService $shopifyApiService) {
        $this->easyApiService = $easyApiService;
        $this->shopifyApiService = $shopifyApiService;
    }
 
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $requestInitialParams = json_decode(session('request_params'), true);
        $settingsCollection = MerchantSettings::getSettingsByShopUrl($requestInitialParams['shop']);
        if('true' ==$requestInitialParams['x_test']) {
            $key = ShopifyApiService::decryptKey($settingsCollection->first()->easy_test_secret_key);
            $url = ShopifyApiService::GET_PAYMENT_DETAILS_URL_TEST_PREFIX . $requestInitialParams['paymentId'];
        } else {
            $key = ShopifyApiService::decryptKey($settingsCollection->first()->easy_secret_key);
            $url = ShopifyApiService::GET_PAYMENT_DETAILS_URL_PREFIX . $requestInitialParams['paymentId'];
        }
        $this->easyApiService->setAuthorizationKey($key);
        $paymentDetailsJson = $this->easyApiService->getPayment($url);
        $paymentDetailsList = json_decode($paymentDetailsJson);
        if(!empty($paymentDetailsList->payment->summary->reservedAmount)) {
            $params['url'] = $request->get('x_url_complete'); 
            $requestInitialParams = json_decode(session('request_params'), true);
            $params['params'] = ['x_account_id'        => $requestInitialParams['x_account_id'],	
                                  'x_amount'            => $requestInitialParams['x_amount'],	
                                  'x_currency'          => $requestInitialParams['x_currency'],	
                                  'x_gateway_reference' => $requestInitialParams['paymentId'],	
                                  'x_reference'         => $requestInitialParams['x_reference'],	
                                  'x_result'            => 'completed',	
                                  'x_timestamp'         => date("Y-m-d\TH:i:s\Z"),
                                  'x_transaction_type'  => 'authorization'];
            $params['params']['x_signature'] = $this->shopifyApiService->calculateSignature($params['params'], $requestInitialParams['gateway_password']);
            return view('easy-accept', $params);
        } else {
            return redirect($requestInitialParams['x_url_cancel']);
        }
    }

    protected function makeCallback() 
    {
       $params = json_decode(session('request_params'), true);
       $this->shopifyApiService->paymentCallback($params['x_url_callback'], $params);
    }
}
