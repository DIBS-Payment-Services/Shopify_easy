<?php

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

    public function setX_Message($message) {
        $this->params['x_message'] = $message;
    }

    public function setX_CardType($field) {
        $this->params['x_card_type'] = $field;
    }

    public function setX_CardMaskedPan($field) {
        $this->params['x_card_masked_pan'] = $field;
    }

    /**
     * 
     * @return array
     */
    public function getParams() {
        return $this->params;
    }

}
