<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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
    public function generateRequestParams($settings, $checkout = []);
    
}
