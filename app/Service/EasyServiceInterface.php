<?php

namespace App\Service;

/**
 *
 * @author mabe
 */
interface EasyServiceInterface {
    
    
    /**
     * 
     * @param array $checkout
     * @return array
     */
    public function generateRequestParams(array $settings, \App\CheckoutObject $checkoutObject);
    
}
