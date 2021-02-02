<?php

namespace App\Service;

use App\Exceptions\ShopifyApiException;
use App\Service\Api\Client;
use \Illuminate\Http\Request;

class ShopifyApiService  implements ShopifyApiServiceInterface{

    const OAUTH_ACCESS_TOKEN_URL = '/admin/oauth/access_token';
    const API_SHOP_URL = '/admin/api/api_version/shop.json';
    const API_CHECKOUTS_URL = '/admin/api/api_version/checkouts.json';
    const API_ORDER_URL = '/admin/api/api_version/orders/order_id.json';
    const GET_PAYMENT_DETAILS_URL_PREFIX = 'https://api.dibspayment.eu/v1/payments/';
    const GET_PAYMENT_DETAILS_URL_TEST_PREFIX = 'https://test.api.dibspayment.eu/v1/payments/';

    private $client;
    public function __construct(Client $client) {
      $this->client = $client;
    }

    /**
     *
     * @param Request $request
     * @return string|null
     * @throws ShopifyApiException
     */
    public function auth(Request $request) {
       $shop = $request->get('shop');
       $apiKey = env('SHOPIFY_API_KEY');
       $apiSecret = env('SHOPIFY_API_SECRET');
       $fields = ['client_id' => $apiKey,
                  'client_secret' => $apiSecret,
                  'code' => $request->get('code')];
        $url = "https://$shop" . self::OAUTH_ACCESS_TOKEN_URL;
        $this->client->post($url, $fields);
        return $this->handleResponse($this->client);
    }

    /**
     *
     * @param string $acessToken
     * @param string $shopUrl
     * @return string|null
     * @throws ShopifyApiException
     */
    public function getShopInfo($acessToken, $shopUrl) {
        $this->client->setHeader('X-Shopify-Access-Token', $acessToken);
        $url = $this->getShopUrl($shopUrl);
        $this->client->get($url);
        return $this->handleResponse($this->client);
    }

    /**
     *
     * @param string $acessToken
     * @param string $shopUrl
     * @return string|null
     * @throws ShopifyApiException
     */
    public function getOrder(string $acessToken, string $shopUrl, string $orderId) {
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
     * @throws ShopifyApiException
     */
    public function getCheckoutById(string $acessToken, string $shopUrl, string $checkoutId) {
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
     * @throws ShopifyApiException
     */
    public function paymentCallback(string $url, array $params, string $type = null) {
        $this->client->post($url, $params);
        $this->handleResponse($this->client);
    }

    /**
     *
     * @param array $params
     * @return array
     */
    public function calculateSignature(array $params, string $gatewayPassword) {
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

   public function registerOrederCreatedHook(string $acessToken, string $shopUrl) {
       $url = $this->getRegisterOrderWebhookUrl($shopUrl);
       $this->client->setHeader('X-Shopify-Access-Token', $acessToken);
       $appUrl = env('SHOPIFY_APP_URL');
       $address = 'https://' . $appUrl . '/order_created';
       $params = ['webhook'=>['topic'=>'orders/create', 'address' => $address, 'format' => 'json']];
       try{
        $this->client->post($url, $params);
        $resp = $this->handleResponse($this->client);
       } catch(ShopifyApiException $e) {

       }
   }

    /**
     * set transaction to paid status for mobilepay and Swish payments
     * @param string $accessToken
     * @param string $shopUrl
     * @param string $transactionId
     * @param string $orderId
     * @throws ShopifyApiException
     */
   public function payTransaction(string $accessToken, string $shopUrl, string $transactionId, string $orderId) {
       $this->client->setHeader('X-Shopify-Access-Token', $accessToken);
       $this->client->setHeader('Content-Type', 'application/json');
       $url = $this->getPayTransactionUrl($shopUrl, $orderId);
       $capture_data = array(
           'transaction' =>
               array(
                   'kind' => 'capture',
                   'source' => 'external',
                   'authorization' => $transactionId
               )
       );
       $this->client->post($url, json_encode($capture_data));
       $this->handleResponse($this->client);
   }

   public static function encryptKey($data) {
        return openssl_encrypt($data, 'AES-128-ECB', env('EASY_KEY_SALT'));
   }

   public static function decryptKey($encryptedData) {
        return openssl_decrypt($encryptedData, 'AES-128-ECB', env('EASY_KEY_SALT'));
   }

   private function getCheckoutsUrl(string $shopUrl) {
      return str_replace('api_version', env('SHOPIFY_API_VERSION') ,
              'https://' . $shopUrl . self::API_CHECKOUTS_URL);
   }

   private function getShopUrl(string $shopUrl) {
      return str_replace('api_version', env('SHOPIFY_API_VERSION') ,
              'https://' . $shopUrl . self::API_SHOP_URL);
   }

   private function getOrderUrl(string $shopUrl, string $order_id) {
      return str_replace(['api_version', 'order_id'], [env('SHOPIFY_API_VERSION'), $order_id] ,
              'https://' . $shopUrl . self::API_ORDER_URL);
   }

   private function getOrderCheckoutUrl(string $shopUrl) {
       return str_replace('api_version', env('SHOPIFY_API_VERSION') ,
              'https://' . $shopUrl . '/admin/api/api_version/orders.json' );
   }

   private function getRegisterOrderWebhookUrl(string $shopUrl) {
       return str_replace('api_version', env('SHOPIFY_API_VERSION') ,
              'https://' . $shopUrl . '/admin/api/2019-04/webhooks.json' );
   }

   private function getPayTransactionUrl(string $shopUrl, string $orderId) {
       return str_replace(['api_version', 'order_id'], [env('SHOPIFY_API_VERSION'), $orderId],
           'https://' . $shopUrl . '/admin/api/api_version/orders/order_id/transactions.json' );
   }

   protected function handleResponse(Client $client) {
      if($client->isSuccess()) {
          return $client->getResponse();
      } else {
          $errorMessage = $client->getHttpStatus() ? $client->getResponse() : $client->getErrorMessage();
          throw new ShopifyApiException($errorMessage, $client->getHttpStatus());
      }
   }
}
