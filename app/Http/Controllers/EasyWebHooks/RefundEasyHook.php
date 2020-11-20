<?php

namespace App\Http\Controllers\EasyWebHooks;

use App\Exceptions\ExceptionHandler;
use App\Exceptions\ShopifyApiExceptionHandler;
use App\Http\Controllers\Controller;
use App\ShopifyReturnParams;
use Illuminate\Http\Request;
use App\Service\ShopifyApiService;
use App\PaymentDetails;
use App\MerchantSettings;
use Illuminate\Http\Response;

class RefundEasyHook extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @param ShopifyApiService $shopifyApiService
     * @param ShopifyReturnParams $shopifyReturnParams
     * @param ShopifyApiExceptionHandler $ehsh
     * @param ExceptionHandler $handler
     * @return Response
     */
    public function __invoke(Request $request,
                             ShopifyApiService $shopifyApiService,
                             ShopifyReturnParams $shopifyReturnParams,
                             ShopifyApiExceptionHandler $ehsh,
                             ExceptionHandler $handler)
    {
        try{
              $data = $request->get('data');
              $paymentDetails = PaymentDetails::getDetailsByPaymentId($data['paymentId']);
              $refundRequestParams = json_decode($paymentDetails->first()->refund_request_params, true);
              if(!empty($refundRequestParams)) {
                  $refundId = $data['refundId'];
                  $shopifyReturnParams->setX_GatewayReference($refundId);
                  $shopifyReturnParams->setX_Reference($refundRequestParams['x_reference']);
                  $shopifyReturnParams->setX_Result('completed');
                  $shopifyReturnParams->setX_Timestamp(date("Y-m-d\TH:i:s\Z"));
                  $shopifyReturnParams->setX_TransactionType('refund');
                  $settingsCollection = MerchantSettings::getSettingsByShopOrigin($paymentDetails->first()->shop_url);
                  $pass = $settingsCollection->first()->gateway_password;
                  $shopifyReturnParams->setX_Signature($shopifyApiService->calculateSignature($shopifyReturnParams->getParams(),$pass));
                  $shopifyApiService->paymentCallback($refundRequestParams['x_url_callback'], $shopifyReturnParams->getParams());
             }
        } catch(\App\Exceptions\ShopifyApiException $e) {
            $ehsh->handle($e, $request->all());
            return response('HTTP/1.0 500 Internal Server Error', 500);
        } catch(\Exception $e) {
            $handler->report($e);
            return response('HTTP/1.0 500 Internal Server Error', 500);
        }
    }
}
