<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Service\ShopifyApiService;

use Validator;

class MerchantSettings extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(session('shop_url')) {
            $shop = \App\MerchantSettings::getSettingsByShopUrl(session('shop_url'));
            $params = current($shop->toArray());
            $params['easy_secret_key'] = ShopifyApiService::decryptKey($params['easy_secret_key']);
            $params['easy_test_secret_key'] = ShopifyApiService::decryptKey($params['easy_test_secret_key']);
            $params['lang'] = ["en-GB" => "English", 
                                   "sv-SE"=> "Swedish", 
                                   "nb-NO" => "Norwegian", 
                                   "da-DK" => "Danish"];
            $params['act'] = ["b2c" => "B2C only",
                              "b2b" => "B2B only", 
                              "b2c_b2b_b2c" => "B2C & B2B (defaults to B2C)", 
                              "b2b_b2c_b2b" => "B2B & B2C (defaults to B2B)" ];
            $params['gateway_install_link'] = env('EASY_GATEWAY_INSTALL_URL');
            $params['shop_origin'] = session('shop_url');
            $params['action_url'] = 'https://' . env('SHOPIFY_APP_URL') . '/postForm';
            $params['install_gateway_redirect'] = env('EASY_GATEWAY_INSTALL_URL');
            
            return view('easy-settings-form', $params);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $rules = [
            'terms_and_conditions_url' => 'required',
            'terms_and_conditions_url'  =>'url',
            'easy_secret_key'  => 'required',
            'easy_test_secret_key' =>  'required',
            'easy_merchantid' => 'required'];

        // TODO validation!
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            error_log('passed');
        } else {
            error_log('failed');
        }
        \App\MerchantSettings::saveShopSettings($request);
    }
}
