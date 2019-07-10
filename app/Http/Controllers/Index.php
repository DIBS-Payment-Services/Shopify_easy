<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Description of Index
 *
 * @author mabe
 */
class Index extends Controller {
    
    const GRANT_ACCESS_SCOPE = 'read_checkouts,read_products,read_orders';
    
    public function index(Request $request)
    {
        $url = url('/install');
        return view('install', ['url' => $url]);
    }
    
    public function install(Request $request) {
        $request->validate([
            'shop' => ['required', 'url']]);
        
        $apiKey = env('SHOPIFY_API_KEY');
        $appUrl = env('SHOPIFY_APP_URL');
        $shopUrl = $request->get('shop');
        $install_url = "{$shopUrl}/admin/oauth/authorize?client_id={$apiKey}&scope=".self::GRANT_ACCESS_SCOPE."" .
                "&redirect_uri=https://{$appUrl}/auth";
        return redirect($install_url);
    }

}
