<?php

namespace App\Service;

/**
 * Description of EasyApiService
 *
 * @author mabe
 */
class EasyApiService implements EasyApiServiceInterface{

    const PAYMENT_API_TEST_URL = 'https://test.api.dibspayment.eu/v1/payments';
    const PAYMENT_API_URL = 'https://api.dibspayment.eu/v1/payments';
    const GET_PAYMENT_DETAILS_URL_PREFIX = 'https://api.dibspayment.eu/v1/payments/';
    const GET_PAYMENT_DETAILS_URL_TEST_PREFIX = 'https://test.api.dibspayment.eu/v1/payments/';
    const CHARGE_PAYMENT_URL_PREFIX  = 'https://api.dibspayment.eu/v1/payments/';
    const CHARGE_PAYMENT_URL_TEST_PREFIX  = 'https://test.api.dibspayment.eu/v1/payments/';

    public $curl;

    public function __construct() {
      $curl = new \Curl\Curl();
      $curl->setHeader('Content-Type', 'text/json');
      $curl->setHeader('Accept', 'test/json');
      $this->curl = $curl;
    }

    public function setAuthorizationKey($key) {
      $this->curl->setHeader('Authorization', str_replace('-', '', trim($key)));
    }

    public function createPayment($url, $data) {
      $this->curl->setHeader('commercePlatformTag:', 'easy_shopify_inject');
      $this->curl->post($url, $data);
      if($this->curl->isSuccess()) {
          return $this->curl->getResponse();
      } else {
          throw new \App\Exceptions\EasyException($this->curl->getResponse(), $this->curl->getHttpStatus());
      }
      
    }

    public function getPayment($url) {
      $this->curl->get($url);
      if($this->curl->isSuccess()) {
          return $this->curl->getResponse();
      } else {
          error_log('Getting payment Error');
          throw new \App\Exceptions\EasyException($this->curl->getResponse());
      }
    }

   public function updateReference($url, $data) {
      $this->curl->put($url, $data, true);
      if($this->curl->isSuccess()) {
           error_log('reference updated');
          return $this->curl->getResponse();
      } else {
          throw new \App\Exceptions\EasyException($this->curl->getResponse());
      }
    }

    public function chargePayment($url, $data) {
      $this->curl->post($url, $data);
      if($this->curl->isSuccess()) {
          error_log('transaction charged');
          return $this->curl->getResponse();
      } else {
          error_log('transaction charge failed');
          error_log($this->curl->getResponse());
          throw new \App\Exceptions\EasyException($this->curl->getResponse(), $this->curl->getHttpStatus());
      }
    }

    public function refundPayment() {
        
    }

}
