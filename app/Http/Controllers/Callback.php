<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\ShopifyApiService;
use App\PaymentDetails;

use App\MerchantSettings;

class Callback extends Controller
{
    protected $shopifyApiService;

    public function __construct(ShopifyApiService $service) {
        $this->shopifyApiService = $service;
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
         error_log('Callback started');
        try{
            $request->get('param1');
            $url = $request->get('callback_url');
            $data = $request->get('data');
            $params = ['x_account_id'        => $request->get('merchantId'),	
                        'x_amount'            => $data['amount']['amount'] / 100,	
                        'x_currency'          => $data['amount']['currency'],	
                        'x_gateway_reference' => $data['paymentId'],	
                        'x_reference'         => $request->get('x_reference'),	
                        'x_result'            => 'completed',	
                        'x_timestamp'         => date("Y-m-d\TH:i:s\Z"),
                        'x_transaction_type'  => 'authorization'];
            $paymentDetailsCollection = PaymentDetails::getDetailsByCheckouId($request->get('x_reference'));
            $shopUrl = $paymentDetailsCollection->first()->shop_url;
            $merchantSettingsCollection = MerchantSettings::getSettingsByShopUrl($shopUrl);
            $gatewayPassword = $merchantSettingsCollection->first()->gateway_password;
            $params1 = $params;
            $params['x_signature'] = $this->shopifyApiService->calculateSignature($params1, $gatewayPassword);
            $this->shopifyApiService->paymentCallback($url, $params);
            header("HTTP/1.1 200 OK");
        } catch( \Exception $e) {
            error_log('Callback filed');
            header("HTTP/1.1 500 Callback filed");
        }
    }
}
