<?php

namespace App\Service;

/**
 *
 * @author mabe
 */
interface EasyApiServiceInterface {
   public function getPayment(string $paymentId);
   
   public function createPayment(string $data);

   public function chargePayment(string $pyamentId, string $data);

   public function refundPayment(string $pyamentId, string $data);
}
