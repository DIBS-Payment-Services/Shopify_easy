<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App;

/**
 * Description of OrderItem
 *
 * @author mabe
 */
class OrderItem {
    
    private $item;
    
    public function __construct(array $item, $taxIncluded = true) {
        $this->item = $item;
    }

    public function getReference() {
        return $this->item['product_id'];
    }

    public function getName() {
        return str_replace(array('\'', '&'), '', $this->item['title']);
    }

    public function getQuantity() {
        return $this->item['quantity'];
    }

    public function getUnit() {
        return 'pcs';
    }

    public function getPrice() {
        
    }

    public function getTaxRate() {
        
    }

    public function getTaxAmount() {
        
    }

    public function getGrossTotalAmount() {
        
    }

    public function getNetTotalAmount() {
        
    }
}
