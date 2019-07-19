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
    protected $fillable = ['checkout_id', 'dibs_paymentid', 'shop_url', 'test', 'create_payment_items_params'];

    public static function getDetailsByCheckouId($checkoutId) {
       return self::query()->where('checkout_id', $checkoutId)->get();
    }

    public static function addOrUpdateDetails($params) {
        self::query()->updateOrCreate(['checkout_id' => $params['checkout_id']], $params);
    }

    public static function getDetailsByPaymentId($paymentId) {
       return self::query()->where('dibs_paymentid', $paymentId)->get();
    }

     public static function setCaptureRequestParams($checkoutid, $data) {
        self::query()->where(['checkout_id' => $checkoutid])->update(['capture_request_params' => $data]);
    }
}
