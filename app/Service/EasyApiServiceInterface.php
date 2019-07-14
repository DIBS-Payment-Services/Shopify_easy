<?php

namespace App\Service;

/**
 *
 * @author mabe
 */
interface EasyApiServiceInterface {
   public function getPayment($paymentId);
   
   public function createPayment($data);
   
   public function chargePayment($pyamentId, $data);
   
   public function refundPayment();
}
