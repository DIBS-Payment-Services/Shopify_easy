<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Service\ShopifyApiService;

use App\MerchantSettings;

/**
 * Description of ChargeCreatedEasyHook
 *
 * @author mabe
 */
class ChargeCreatedEasyHook extends Controller {
    
     /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, ShopifyApiService $shopifyApiService)
    {
        
        ob_start();
            var_dump($request->all());
            $result = ob_get_clean();
        error_log($result);
        
        $data = $request->get('data');
        $params = [
                  'x_gateway_reference' => $data['paymentId'],
                  'x_reference'         => $request->get('x_reference'),
                  'x_result'            => 'completed',
                  'x_timestamp'         => date("Y-m-d\TH:i:s\Z"),
                  'x_transaction_type'  => 'capture',
                  'x_message' => ''];
         $settingsCollection = MerchantSettings::getSettingsByShopUrl($request->get('shop_url'));
         $params['x_signature'] = $shopifyApiService->calculateSignature($params, $settingsCollection->first()->gateway_password);
         
         $url = $request->get('callback_url');
         
         $shopifyApiService->paymentCallback($url, $params);
        
    }
    
    
}
