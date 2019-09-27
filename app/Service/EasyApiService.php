<?php

namespace App\Service;

/**
 * Description of EasyApiService
 *
 * @author mabe
 */
class EasyApiService implements EasyApiServiceInterface{

    const ENDPOINT_TEST = 'https://test.api.dibspayment.eu/v1/payments/';
    const ENDPOINT_LIVE = 'https://api.dibspayment.eu/v1/payments/';

    const ENDPOINT_TEST_CHARGES = 'https://test.api.dibspayment.eu/v1/charges/';
    const ENDPOINT_LIVE_CHARGES = 'https://api.dibspayment.eu/v1/charges/';

    const ENV_LIVE = 'live';
    const ENV_TEST = 'test';

    /**
     *
     * @var \App\Service\Api\Client
     */
    private $client;

    private $env;

    public function __construct(\App\Service\Api\Client $client) {
      $this->client = $client;
      $this->client->setHeader('Content-Type', 'text/json');
      $this->client->setHeader('Accept', 'test/json');
      $this->setEnv(self::ENV_LIVE);
    }

    public function setEnv(string $env = self::ENV_LIVE) {
        $this->env = $env;
    }

    public function getEnv() {
        return $this->env;
    }

    public function setAuthorizationKey(string $key) {
      $this->client->setHeader('Authorization', str_replace('-', '', trim($key)));
    }

    /**
     * 
     * @param string $data
     * @return \App\Service\Api\Client
     */
    public function createPayment(string $data) {
      $this->client->setHeader('commercePlatformTag:', 'easy_shopify_inject');
      $url = $this->getCreatePaymentUrl();  
      $this->client->post($url, $data);
      return $this->client;
    }

    /**
     * 
     * @param string $paymentId
     * @return \App\Payment
     */
    public function getPayment(string $paymentId) {
      $url = $this->getGetPaymentUrl($paymentId); 
      $this->client->get($url);
      return new \App\Payment($this->handleResponse($this->client));
    }

   public function updateReference(string $paymentId, string $data) {
      $url = $this->getUpdateReferenceUrl($paymentId); 
      $this->client->put($url, $data, true);
      $this->handleResponse($this->client);
    }

    public function chargePayment(string $paymentId, string $data) {
      $url = $this->getChargePaymentUrl($paymentId);
      $this->client->post($url, $data);
      $this->handleResponse($this->client);
    }

    public function refundPayment(string $chargeId, string $data) {
      $url = $this->getRefundPaymentUrl($chargeId);
      $this->client->post($url, $data);
      $this->handleResponse($this->client);
    }

    public function voidPayment(string $paymentId, string $data) {
      $url = $this->getVoidPaymentUrl($paymentId);
      $this->client->post($url, $data);
      $this->handleResponse($this->client);
    }

    protected function handleResponse(\App\Service\Api\Client $client) {
      if($client->isSuccess()) {
          return $client->getResponse();
      } else {
          $errorMessage = $client->getResponse();
          if(0 == $client->getHttpStatus()) {
              $errorMessage = $client->getErrorMessage();
          }
          throw new \App\Exceptions\EasyException($errorMessage, $client->getHttpStatus());
      }
    }

    protected function getCreatePaymentUrl() {
       return ($this->getEnv() == self::ENV_LIVE) ?
               self::ENDPOINT_LIVE : self::ENDPOINT_TEST;
    }

    protected function getGetPaymentUrl(string $paymentId) {
        return ($this->getEnv() == self::ENV_LIVE) ?
                self::ENDPOINT_LIVE . $paymentId:
                self::ENDPOINT_TEST . $paymentId;
    }

    public function getUpdateReferenceUrl(string $paymentId) {
        return ($this->getEnv() == self::ENV_LIVE) ?
                self::ENDPOINT_LIVE . $paymentId .'/referenceinformation':
                self::ENDPOINT_TEST . $paymentId .'/referenceinformation';
    }

    public function getChargePaymentUrl(string $paymentId) {
        return ($this->getEnv() == self::ENV_LIVE) ?
            self::ENDPOINT_LIVE . $paymentId . '/charges':
            self::ENDPOINT_TEST . $paymentId . '/charges';
    }

    public function getVoidPaymentUrl(string $paymentId) {
        return ($this->getEnv() == self::ENV_LIVE) ? 
                self::ENDPOINT_LIVE . $paymentId . '/cancels':
                self::ENDPOINT_TEST . $paymentId . '/cancels';
    }

    public function getRefundPaymentUrl(string $chargeId) {
        return ($this->getEnv() == self::ENV_LIVE) ? 
               self::ENDPOINT_LIVE_CHARGES . $chargeId . '/refunds':
               self::ENDPOINT_TEST_CHARGES . $chargeId . '/refunds';
    }
}
