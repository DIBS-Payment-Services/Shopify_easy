<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Description of PaymentDetails
 *
 * @author mabe
 */
class PaymentDetails extends Model {
    
    protected $table = 'payment_details';
    protected $fillable = ['checkout_id', 'dibs_paymentid', 'shop_url', 'test']; 
    
    public static function getDetailsByCheckouId($checkoutId) {
       return self::query()->where('checkout_id', $checkoutId)->get();
    }
    
    public static function addOrUpdateDetails($params) {
        self::query()->updateOrCreate(['checkout_id' => $params['checkout_id']], $params);
    }
}
