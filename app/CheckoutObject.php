<?php

namespace App;

/**
 * DTO CheckoutObject
 *
 * @author mabe
 */
class CheckoutObject {

    private $checkout;

    const ALLOWED_CHARACTERS_PATTERN = '/[^\x{00A1}-\x{00AC}\x{00AE}-\x{00FF}\x{0100}-\x{017F}\x{0180}-\x{024F}'
                                      . '\x{0250}-\x{02AF}\x{02B0}-\x{02FF}\x{0300}-\x{036F}'
                                      . 'A-Za-z0-9\!\#\$\%\(\)*\+\,\-\.\/\:\;\\=\?\@\[\]\\^\_\`\{\}\~ ]+/u';

    public function setCheckout(array $checkout) {
        $this->checkout = $checkout;
    }

    public function getAmount() {
        return (int)round($this->checkout['total_price'] * 100);
    }

    public function getCurrency() {
        return $this->checkout['presentment_currency'];
    }

    public function getIso2countryCode() {
        if(!empty($this->checkout['shipping_address']['country_code'])) {
            return $this->checkout['shipping_address']['country_code'];
        }
        if(!empty($this->checkout['billing_address']['country_code'])) {
            return $this->checkout['billing_address']['country_code'];
        }
    }

    public function getCustomerPhone() {
        return isset($this->checkout['customer']['phone'])
                    ? $this->checkout['customer']['phone'] : null;
    }

    public function getShippingAddresPhone() {
        if(!empty($this->checkout['shipping_address']['phone'])) {
            return $this->prepareString($this->checkout['shipping_address']['phone']);
        }
    }

    public function getBillinAddresPhone() {
        if(!empty($this->checkout['billing_address']['phone'])) {
            return $this->prepareString($this->checkout['billing_address']['phone']);
        }
    }

    public function getAddressLine1() {
        if(!empty($this->checkout['shipping_address']['address1'])) {
            return $this->prepareString($this->checkout['shipping_address']['address1']);
        }
        if(!empty($this->checkout['billing_address']['address1'])) {
            return $this->prepareString($this->checkout['billing_address']['address1']);
        }
    }

    public function getAddressLine2() {
        if(!empty($this->checkout['shipping_address']['address2'])) {
            return $this->prepareString($this->checkout['shipping_address']['address2']);
        }
        if(!empty($this->checkout['billing_address']['address2'])) {
             return $this->prepareString($this->checkout['billing_address']['address2']);
        }
    }

    public function getPostalCode() {
        if(!empty($this->checkout['shipping_address']['zip'])) {
            return $this->checkout['shipping_address']['zip'];
        }
        if(!empty($this->checkout['billing_address']['zip'])) {
            return $this->checkout['billing_address']['zip'];
        }
   }

    public function getCity() {
        if(!empty($this->checkout['shipping_address']['city'])) {
            return $this->prepareString($this->checkout['shipping_address']['city']);
        }
        if(!empty($this->checkout['billing_address']['city'])) {
            return $this->prepareString($this->checkout['billing_address']['city']);
        }
    }

    public function getCompany() {
        if(!empty($this->checkout['shipping_address']['company'])) {
            return $this->prepareString($this->checkout['shipping_address']['company']);
        }
        if(!empty($this->checkout['billing_address']['company'])) {
            return $this->prepareString($this->checkout['billing_address']['company']);
        }
    }

    public function getCustomerEmail() {
        return $this->checkout['customer']['email'];
    }

    public function getCustomerFirstName() {
        $firstName = '';
        if(!empty($this->checkout['customer']['first_name'])) {
            $firstName = $this->prepareString($this->checkout['customer']['first_name']);
        }elseif (!empty($this->checkout['shipping_address']['first_name'])) {
            $firstName = $this->prepareString($this->checkout['shipping_address']['first_name']);
        }elseif (!empty($this->checkout['billing_address']['first_name'])) {
            $firstName = $this->prepareString($this->checkout['billing_address']['first_name']);
        }
        return !empty($firstName) ? $firstName : 'FirstName';
    }

    public function getcustomerLastName() {
        $lastName = '';
        if(!empty($this->checkout['customer']['last_name'])) {
            $lastName = $this->prepareString($this->checkout['customer']['last_name']);
        }elseif (!empty($this->checkout['shipping_address']['last_name'])) {
            $lastName = $this->prepareString($this->checkout['shipping_address']['last_name']);
        }elseif (!empty($this->checkout['billing_address']['last_name'])) {
            $lastName = $this->prepareString($this->checkout['billing_address']['last_name']);
        }
        return !empty($lastName) ? $lastName : 'LastName';
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

    public function isTaxesIncluded() {
        return $this->checkout['taxes_included'];
    }

    public function getTotalTax() {
        return $this->checkout['total_tax'];
    }

    protected function prepareString($tring) {
        $string = substr($tring, 0, 128);
        return preg_replace(self::ALLOWED_CHARACTERS_PATTERN, '', $string);
    }

}
