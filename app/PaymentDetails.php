<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Service\ShopifyApiService;

/**
 * Description of PaymentDetails
 *
 * @author mabe
 */
class PaymentDetails extends Model {
    
    protected $table = 'payment_details';
    
    public static function getDetailsByCheckouId($checkoutId) {
       return self::query()->where('checkout_id', $checkoutId)->get();
    }
}
