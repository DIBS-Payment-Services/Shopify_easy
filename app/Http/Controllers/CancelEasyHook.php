<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\ShopifyApiService;
use App\PaymentDetails;
use App\MerchantSettings;

class CancelEasyHook extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request,
                             ShopifyApiService $shopifyApiService,
                             \App\ShopifyReturnParams $shopifyReturnParams,
                             \App\Exceptions\ShopifyApiExceptionHandler $ehsh,
                             \App\Exceptions\Handler $handler)
    {
         try{
              $data = $request->get('data');
              $paymentDetails = PaymentDetails::getDetailsByPaymentId($data['paymentId']);
              $cancelRequestParams = json_decode($paymentDetails->first()->cancel_request_params, true);
              if(!empty($cancelRequestParams)){

                  $cancelId = $data['cancelId'];
                  $shopifyReturnParams->setX_GatewayReference($cancelId);
                  $shopifyReturnParams->setX_Reference($cancelRequestParams['x_reference']);
                  $shopifyReturnParams->setX_Result('completed');
                  $shopifyReturnParams->setX_Timestamp(date("Y-m-d\TH:i:s\Z"));
                  $shopifyReturnParams->setX_TransactionType('void');
                  $settingsCollection = MerchantSettings::getSettingsByShopUrl($paymentDetails->first()->shop_url);
                  $pass = $settingsCollection->first()->gateway_password;
                  $shopifyReturnParams->setX_Signature($shopifyApiService->calculateSignature($shopifyReturnParams->getParams(),$pass));
                  $shopifyApiService->paymentCallback($cancelRequestParams['x_url_callback'], $shopifyReturnParams->getParams());
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
