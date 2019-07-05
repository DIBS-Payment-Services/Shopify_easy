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
interface EasyApiServiceInterface {
   public function getPayment($url);
   
   public function createPayment($url, $data);
   
   public function chargePayment();
   
   public function refundPayment();
}
