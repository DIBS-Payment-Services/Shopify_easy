<?php

namespace App;

/**
 * DTO CheckoutObject
 *
 * @author mabe
 */
class CheckoutObject {

    private $checkout;

    public function setCheckout(array $checkout) {
        $this->checkout = $checkout;
    }

    public function getAmount() {
        return round($this->checkout['total_price'] * 100);
    }

    public function getCurrency() {
        return $this->checkout['presentment_currency'];
    }

    public function getIso2countryCode() {
        return isset($this->checkout['shipping_address']['country_code']) ?
                     $this->checkout['shipping_address']['country_code'] :
                     $this->checkout['billing_address']['country_code'];

    }

    public function getCustomerPhone() {
        return $this->checkout['customer']['phone'];
    }

    public function getShippingAddresPhone() {
        return isset($this->checkout['shipping_address']['phone']) ?
                     $this->checkout['shipping_address']['phone'] : null;
    }

    public function getBillinAddresPhone() {
        return $this->checkout['billing_address']['phone'];
    }

    public function getAddressLine1() {
        return isset($this->checkout['shipping_address']['address1']) ?
                     $this->checkout['shipping_address']['address1'] :
                     $this->checkout['billing_address']['address1'];
    }

    public function getAddressLine2() {
        return isset($this->checkout['shipping_address']['address2']) ?
                     $this->checkout['shipping_address']['address2'] :
                     $this->checkout['billing_address']['address2'];
    }

    public function getPostalCode() {
        return isset($this->checkout['shipping_address']['zip']) ?
                     $this->checkout['shipping_address']['zip'] :
                     $this->checkout['billing_address']['zip'];
    }

    public function getCity() {
        return isset($this->checkout['shipping_address']['city']) ?
                     $this->checkout['shipping_address']['city'] :
                     $this->checkout['billing_address']['city'];
    }

    public function getCustomerEmail() {
        return $this->checkout['customer']['email'];
    }

    public function getCustomerFirstName() {
        return $this->checkout['customer']['first_name'];
    }

    public function getcustomerLastName() {
        return $this->checkout['customer']['last_name'];
    }

    public function getLineItems() {
        return $this->checkout['line_items'];
    }

    public function getShippingLines() {
        return $this->checkout['shipping_lines'];
    }

    public function getTotalDiscounts() {
        return $this->checkout['total_discounts'];
    }

    public function isTaxesInleded() {
        return $this->checkout['taxes_included'];
    }

    public function getTotalTax() {
        return $this->checkout['total_tax'];
    }

}
