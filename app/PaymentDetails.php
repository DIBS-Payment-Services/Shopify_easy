<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Description of PaymentDetails
 *
 * @author mabe
 */
class PaymentDetails extends Model {

    protected $table = 'payment_details';
    protected $fillable = ['amount', 'currency', 'checkout_id', 'dibs_paymentid', 'shop_url', 'test', 'create_payment_items_params'];

    public static function getDetailsByCheckouId($checkoutId)
    {
        $result = self::query()->select(['shop_url', 'dibs_paymentid', 'amount', 'currency', 'dibs_paymentid', 'checkout_id', 'test'])->where('checkout_id', $checkoutId)->get();
        DB::disconnect();
        return $result;
    }

    public static function getDetailsBytId($id) {
        return self::query()->where('id', $id)->get();
    }

    public static function addOrUpdateDetails($params) {
        self::query()->insert($params);
        $result = DB::getPdo()->lastInsertId();
        DB::disconnect();
        return $result;
    }

    public static function getDetailsByPaymentId($paymentId) {
       $result =  self::query()->where('dibs_paymentid', $paymentId)->get();
        DB::disconnect();
        return $result;
    }

    public static function persistCaptureRequestParams($checkoutid, $data) {
        self::query()->where(['checkout_id' => $checkoutid])->update(['capture_request_params' => $data]);
        DB::disconnect();
    }

    public static function persistRefundRequestParams($checkoutid, $data) {
        self::query()->where(['checkout_id' => $checkoutid])->update(['refund_request_params' => $data]);
        DB::disconnect();
    }

    public static function persistCancelRequestParams($checkoutid, $data) {
        self::query()->where(['checkout_id' => $checkoutid])->update(['cancel_request_params' => $data]);
        DB::disconnect();
    }


}
