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

    private $client;
    private $env;

    public function __construct(\App\Service\Api\Client $client) {
      $this->client = $client;
      $this->client->setHeader('Content-Type', 'text/json');
      $this->client->setHeader('Accept', 'test/json');
      $this->setEnv(self::ENV_LIVE);
    }

    public function setEnv($env = self::ENV_LIVE) {
        $this->env = $env;
    }

    public function getEnv() {
        return $this->env;
    }

    public function setAuthorizationKey($key) {
      $this->client->setHeader('Authorization', str_replace('-', '', trim($key)));
    }

    public function createPayment($data) {
      $this->client->setHeader('commercePlatformTag:', 'easy_shopify_inject');
      $url = $this->getCreatePaymentUrl();  
      $this->client->post($url, $data);
      return $this->handleResponse($this->client);
    }

    public function getPayment($paymentId) {
      $url = $this->getGetPaymentUrl($paymentId); 
      $this->client->get($url);
      return $this->handleResponse($this->client);
    }

   public function updateReference($paymentId, $data) {
      $url = $this->getUpdateReferenceUrl($paymentId); 
      $this->client->put($url, $data, true);
      $this->handleResponse($this->client);
    }

    public function chargePayment($paymentId, $data) {
      $url = $this->getChargePaymentUrl($paymentId);
      $this->client->post($url, $data);
      $this->handleResponse($this->client);
    }

    public function refundPayment($chargeId, $data) {
      $url = $this->getRefundPaymentUrl($chargeId);
      $this->client->post($url, $data);
      $this->handleResponse($this->client);
    }

    public function voidPayment($paymentId, $data) {
      $url = $this->getVoidPaymentUrl($paymentId);
      $this->client->post($url, $data);
      $this->handleResponse($this->client);
    }

    protected function handleResponse(\App\Service\Api\Client $client) {
      if($client->isSuccess()) {
          return $client->getResponse();
      } else {
          throw new \App\Exceptions\EasyException($client->getResponse(), $client->getHttpStatus());
      }
    }

    protected function getCreatePaymentUrl() {
        if($this->getEnv() == self::ENV_LIVE) {
            return self::ENDPOINT_LIVE;
        } else{
            return self::ENDPOINT_TEST;
        }
    }

    protected function getGetPaymentUrl($paymentId) {
        if($this->getEnv() == self::ENV_LIVE) {
            return self::ENDPOINT_LIVE . $paymentId;
        } else{
            return self::ENDPOINT_TEST . $paymentId;
        }
    }

    public function getUpdateReferenceUrl($paymentId) {
        if($this->getEnv() == self::ENV_LIVE) {
            return self::ENDPOINT_LIVE . $paymentId .'/referenceinformation';
        } else{
            return self::ENDPOINT_TEST . $paymentId .'/referenceinformation';
        }
    }

    public function getChargePaymentUrl($paymentId) {
        if($this->getEnv() == self::ENV_LIVE) {
            return self::ENDPOINT_LIVE . $paymentId . '/charges';
        } else{
            return self::ENDPOINT_TEST . $paymentId . '/charges';
        }
    }

    public function getVoidPaymentUrl($paymentId) {
        if($this->getEnv() == self::ENV_LIVE) {
            return self::ENDPOINT_LIVE . $paymentId . '/cancels';
        } else{
            return self::ENDPOINT_TEST . $paymentId . '/cancels';
        }
    }

    public function getRefundPaymentUrl($chargeId) {
        if($this->getEnv() == self::ENV_LIVE) {
            return self::ENDPOINT_LIVE_CHARGES . $chargeId . '/refunds';
        } else{
            return self::ENDPOINT_TEST_CHARGES . $chargeId . '/refunds';
        }
    }
}
