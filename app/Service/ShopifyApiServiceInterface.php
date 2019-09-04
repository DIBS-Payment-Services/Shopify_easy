<?php

namespace App\Service;

/**
 *
 * @author mabe
 */
interface ShopifyApiServiceInterface {
    
    public function auth(\Illuminate\Http\Request $request);
    
    public function getShopInfo($acessToken, $shop);
    
    public function getCheckoutById(string $acessToken, string $shop , string $checkoutId);
    
    public function getOrder(string $acessToken, string $shopUrl, string $orderId);
    
    public function paymentCallback(string $url, array $params, string $type = null);
}
