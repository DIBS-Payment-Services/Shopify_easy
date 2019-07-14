<?php

namespace App\Service;

class ShopifyApiService  implements ShopifyApiServiceInterface{

    const OAUTH_ACCESS_TOKEN_URL = '/admin/oauth/access_token';
    const API_SHOP_URL = '/admin/api/api_version/shop.json';
    const API_CHECKOUTS_URL = '/admin/api/api_version/checkouts.json';
    const API_ORDER_URL = '/admin/api/api_version/orders/order_id.json';
    const GET_PAYMENT_DETAILS_URL_PREFIX = 'https://api.dibspayment.eu/v1/payments/';
    const GET_PAYMENT_DETAILS_URL_TEST_PREFIX = 'https://test.api.dibspayment.eu/v1/payments/';

    private $accessToken;
    private $client;
    public function __construct(\App\Service\Api\Client $client) {
      $this->client = $client;
    }

    /**
     * 
     * @param Request $request
     * @return string|null
     */
    public function auth($request) {
       $shop = $request->get('shop');
       $apiKey = env('SHOPIFY_API_KEY');
       $apiSecret = env('SHOPIFY_API_SECRET');
       $fields = ['client_id' => $apiKey,
                  'client_secret' => $apiSecret,
                  'code' => $request->get('code')];
        $url = "https://$shop" . self::OAUTH_ACCESS_TOKEN_URL;
        $this->client->post($url, $fields);
        
        if($this->client->isSuccess()) {
           $responseObj = json_decode($this->client->getResponse());
           $this->accessToken = $responseObj->access_token;
           error_log($this->client->getResponse());
           return $this->client->getResponse();
        } else {
           error_log($this->client->getResponse());
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
        $this->client->setHeader('X-Shopify-Access-Token', $acessToken);
        $url = $this->getOrderUrl($shopUrl, $orderId);
        $this->client->get($url);
        return $this->handleResponse($this->client);
    }

    /**
     * 
     * @param string $acessToken
     * @param string $shopUrl
     * @param string $checkoutId
     * @return array
     */
    public function getCheckoutById($acessToken, $shopUrl ,$checkoutId) {
        $this->client->setHeader('X-Shopify-Access-Token', $acessToken);
        $filter = $checkoutId - 1;
        $url = $this->getCheckoutsUrl($shopUrl). "?since_id=$filter";
        $this->client->get($url);
        $result = $this->handleResponse($this->client);
        $result_array = json_decode($result, true);
           foreach($result_array['checkouts'] as $checkout) {
                if($checkout['id'] == $checkoutId) {
                    return $checkout;
                }
           }
    }

    /**
     * 
     * @param string $url
     * @param array $params
     */
    public function paymentCallback($url, $params, $type = null) {
        $this->client->post($url, $params);
        $this->handleResponse($this->client);
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

   protected function handleResponse(\App\Service\Api\Client $client) {
      if($client->isSuccess()) {
          return $client->getResponse();
      } else {
          throw new \App\Exceptions\ShopifyApiException($client->getResponse(), $client->getHttpStatus());
      }
   }

}
