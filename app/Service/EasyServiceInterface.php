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
    public function generateRequestParams($settings, \App\CheckoutObject $checkoutObject);
    
}
