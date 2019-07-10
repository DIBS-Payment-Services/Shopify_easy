<?php

namespace App\Service;

/**
 *
 * @author mabe
 */
interface EasyApiServiceInterface {
   public function getPayment($url);
   
   public function createPayment($url, $data);
   
   public function chargePayment($url, $data);
   
   public function refundPayment();
}
