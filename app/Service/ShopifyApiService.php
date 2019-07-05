<?php

namespace App\Service;

use App\MerchantSettings;

class ShopifyApiService  implements ShopifyApiServiceInterface{

    const OAUTH_ACCESS_TOKEN_URL = '/admin/oauth/access_token';
    const API_SHOP_URL = '/admin/api/api_version/shop.json';
    const API_CHECKOUTS_URL = '/admin/api/api_version/checkouts.json';
    const API_ORDER_URL = '/admin/api/api_version/orders/order_id.json';
    const GET_PAYMENT_DETAILS_URL_PREFIX = 'https://api.dibspayment.eu/v1/payments/';
    const GET_PAYMENT_DETAILS_URL_TEST_PREFIX = 'https://test.api.dibspayment.eu/v1/payments/';
   
    private $accessToken;

    /**
     * 
     * @param Request $request
     * @return string|null
     */
    public function auth($request) {
       $curl = new \Curl\Curl();
       $shop = $request->get('shop');
       $apiKey = env('SHOPIFY_API_KEY');
       $apiSecret = env('SHOPIFY_API_SECRET');
       $fields = array(
                'client_id' => $apiKey,
                'client_secret' => $apiSecret,
                'code' => $request->get('code'),
        );
        $url = "https://$shop" . self::OAUTH_ACCESS_TOKEN_URL;
        $curl->post($url, $fields);
        if($curl->isSuccess()) {
           $responseObj = json_decode($curl->getResponse());
           $this->accessToken = $responseObj->access_token;
           error_log($curl->getResponse());
           return $curl->getResponse();
        } else {
           error_log($curl->getResponse());
        }
    }

    /**
     * 
     * @param string $acessToken
     * @param string $shopUrl
     * @return string|null
     */
    public function getShopInfo($acessToken, $shopUrl) {
        $curl = new \Curl\Curl();
        $curl->setHeader('X-Shopify-Access-Token', $acessToken);
        $url = $this->getShopUrl($shopUrl);
        $curl->get($url);
        if($curl->isSuccess()) {
            return $curl->getResponse();
        } else {
            error_log($curl->getResponse());
        }
    }
    
    /**
     * 
     * @param string $acessToken
     * @param string $shopUrl
     * @return string|null
     */
    public function getOrder($acessToken, $shopUrl, $orderId) {
        $curl = new \Curl\Curl();
        $curl->setHeader('X-Shopify-Access-Token', $acessToken);
        $url = $this->getOrderUrl($shopUrl, $orderId);
        $curl->get($url);
        if($curl->isSuccess()) {
            return $curl->getResponse();
        } else {
            error_log($curl->getResponse());
        }
    }
    
    

    /**
     * 
     * @param string $acessToken
     * @param string $shopUrl
     * @param string $checkoutId
     * @return array
     */
    public function getCheckoutById($acessToken, $shopUrl ,$checkoutId) {
        $curl = new \Curl\Curl();
        $curl->setHeader('X-Shopify-Access-Token', $acessToken);
        $date =  date("Y-m-d",strtotime("-1 day")) .  "T00:00:00-00:00";
        
        $previousCheckoutId = $checkoutId - 1;
        
        $url =  $this->getCheckoutsUrl($shopUrl). "?since_id=$previousCheckoutId";
        error_log($url);
        $curl->get($url);
        $result = $curl->getResponse(); 
        if($curl->isSuccess()) {
           $result_array = json_decode($result, true);
           foreach($result_array['checkouts'] as $checkout) {
                error_log($checkout['id'] .'-'. $checkout['created_at']);
                if($checkout['id'] == $checkoutId) {
                    return $checkout;
                }
           }
        } else {
            error_log($curl->getResponse());
        }
    }
    
    public function getOrderByCheckoutId($acessToken, $shopUrl ,$checkoutId) {
        $curl = new \Curl\Curl();
        $curl->setHeader('X-Shopify-Access-Token', $acessToken);
        $url =  $this->getOrderCheckoutUrl($shopUrl);
        $curl->get($url);
        $result = $curl->getResponse(); 
        if($curl->isSuccess()) {
           
          
            echo $checkoutId;
           
           $result_array = json_decode($result, true);
           
           var_dump($result_array);
           
           exit;
           foreach($result_array['orders'] as $order) {
                error_log($order['checkout_id']);
                if($order['checkout_id'] == $checkoutId) {
                    
                    echo 1234;
                    return $checkout;
                }
           }
           
           exit;
           
        } else {
            error_log($curl->getResponse());
        }
    }

    /**
     * 
     * @param string $url
     * @param array $params
     */
    public function paymentCallback($url, $params) {
        $curl = new \Curl\Curl();
        $curl->post($url, $params);
        if($curl->isSuccess()) {
           error_log('callback success...');
        } else {
           error_log('callback failed...');
        }
    }

    /**
     * 
     * @param type $params
     * @return array
     */
    public function calculateSignature($params, $gatewayPassword) {
        $request = $params;
        $requestArr = array();
        foreach($request as $key => $value) {
            if(strstr($key, "x_") ) {
                $requestArr[$key] = $value;
            }
        }
        ksort($requestArr);
        $message = "";
        foreach($requestArr as $key => $value) {
            $message .= $key . $value;
        }
        return hash_hmac('sha256', $message, $gatewayPassword);
   }

   public static function encryptKey($data) {
        return openssl_encrypt($data, 'AES-128-ECB', env('EASY_KEY_SALT'));
   }

   public static function decryptKey($encryptedData) {
        return openssl_decrypt($encryptedData, 'AES-128-ECB', env('EASY_KEY_SALT'));
   }
   
   private function getCheckoutsUrl($shopUrl) {
      return str_replace('api_version', env('SHOPIFY_API_VERSION') ,'https://' . $shopUrl . self::API_CHECKOUTS_URL);
      
   }
   
   private function getShopUrl($shopUrl) {
      return str_replace('api_version', env('SHOPIFY_API_VERSION') ,'https://' . $shopUrl . self::API_SHOP_URL);
      
   }
   
   private function getOrderUrl($shopUrl, $order_id) {
      return str_replace(['api_version', 'order_id'], [env('SHOPIFY_API_VERSION'), $order_id] ,'https://' . $shopUrl . self::API_ORDER_URL);
      
   }
   
   private function getOrderCheckoutUrl($shopUrl) {
       return str_replace('api_version', env('SHOPIFY_API_VERSION') ,'https://' . $shopUrl . '/admin/api/api_version/orders.json' );
   }
   
}
