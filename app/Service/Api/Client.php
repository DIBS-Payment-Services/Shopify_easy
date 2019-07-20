<?php

namespace App\Service\Api;

/**
 * Description of Client
 *
 * @author mabe
 */
class Client {
   
    private $client;
    
    public function __construct( ) {
        $this->init();
    }
    
    protected function init() {
         $this->client = new \Curl\Curl();
    }
    
    public function post($url, $data = array()) {
        return $this->client->post($url, $data);
    }
    
    public function setHeader($key, $value) {
        $this->client->setHeader($key, $value);
    }
    
    public function isSuccess() {
        return $this->client->isSuccess();
    }
    
    public function getResponse() {
       return $this->client->getResponse();
    }
    
    public function get($url, $data = array()) {
        return $this->client->get($url, $data);
    }
    
    public function put($url, $data = array(), $payload = false) {
        return $this->client->put($url, $data, $payload);
    }
    
    public function getHttpStatus() {
        return $this->client->getHttpStatus();
    }
}
