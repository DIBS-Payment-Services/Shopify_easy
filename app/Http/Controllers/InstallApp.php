<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InstallApp extends Controller
{
    const GRANT_ACCESS_SCOPE = 'read_checkouts,read_orders,write_orders';

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $apiKey = env('SHOPIFY_API_KEY');
        $appUrl = env('SHOPIFY_APP_URL');
        $shopUrl = $request->get('shop');
        $install_url = "https://{$shopUrl}/admin/oauth/authorize?client_id={$apiKey}&scope=".self::GRANT_ACCESS_SCOPE."" .
                "&redirect_uri=https://{$appUrl}/auth";
        return redirect($install_url);
    }
}
