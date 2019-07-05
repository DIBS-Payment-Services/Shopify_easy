<?php

namespace App\Service;

/**
 *
 * @author mabe
 */
interface ShopifyApiServiceInterface {
    
    public function auth($request);
    
    public function getShopInfo($acessToken, $shop);
    
    public function getCheckoutById($acessToken, $shop ,$checkoutId);
    
    public function paymentCallback($url, $params); 
}
