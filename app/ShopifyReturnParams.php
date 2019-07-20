<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App;

/**
 * Description of ShopifyReturnParams
 *
 * @author mabe
 */
class ShopifyReturnParams {
    
    private $params = [];
    
    public function setX_Amount($amount) {
        $this->params['x_amount'] = $amount;
    }
    
    public function setX_Currency($currency) {
        $this->params['x_currency'] = $currency;
    }
    
    public function setX_GatewayReference($gatewayReference) {
        $this->params['x_gateway_reference'] = $gatewayReference;
    }
    
    public function setX_Reference($reference) {
        $this->params['x_reference'] = $reference;
    }
    
    public function setX_Result($result) {
        $this->params['x_result'] = $result;
    }
    
    public function setX_Timestamp($timestamp) {
        $this->params['x_timestamp'] = $timestamp;
    }
    
    public function setX_TransactionType($transactionType) {
        $this->params['x_transaction_type'] = $transactionType;
    }
    
    public function setX_AccountId($accountId) {
        $this->params['x_account_id'] = $accountId;
    }
    
    public function setX_Test() {
        $this->params['x_test'] = 'true';
    }
    
    public function setX_Signature($signature) {
        $this->params['x_signature'] = $signature;
    }
    
    public function setX_Message() {
        $this->params['x_message'] = '';
    }
    
    public function getParams() {
        return $this->params;
    }
    
}
