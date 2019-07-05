<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\ShopifyApiService;

use App\PaymentDetails;

class Capture extends Controller
{
    
    
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, ShopifyApiService $shopifyApiService)
    {
        
        $collection = PaymentDetails::getDetailsByCheckouId($request->get('checkout_id'));
        
        error_log($collection->first()->shop_url);
        error_log($request->get('id'));
        //$collection
        ob_start(); 
        var_dump($request->all());
        $result = ob_get_clean();
        error_log($result);
        
    }
}
