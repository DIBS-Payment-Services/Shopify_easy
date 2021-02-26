<?php

namespace App\Http\Controllers;

use App\Exceptions\EasyApiExceptionHandler;
use App\Exceptions\Handler;
use Illuminate\Http\Request;
use App\Service\ShopifyApiService;
use App\PaymentDetails;
use App\MerchantSettings;
use App\Service\EasyApiService;
use App\Exceptions\ShopifyApiException;
use App\Exceptions\ShopifyApiExceptionHandler;
use Illuminate\Http\Response;
use Illuminate\Log\Logger;

/**
 * Description of OrderCreated
 *
 * @author mabe
 */
class OrderCreatedHook extends Controller{

    /**
     * Handle the incoming request.
     *
     * @param EasyApiService $easyApiService
     * @param Request $request
     * @param EasyApiExceptionHandler $eh
     * @param Handler $handler
     * @param Logger $logger
     * @param ShopifyApiService $shopifyApiService
     * @param ShopifyApiExceptionHandler $shopifyApiExceptionHandler
     * @return Response
     */
    public function __invoke(EasyApiService $easyApiService,
                             Request $request,
                             EasyApiExceptionHandler $eh,
                             Handler $handler,
                             Logger $logger,
                             ShopifyApiService $shopifyApiService,
                             ShopifyApiExceptionHandler $shopifyApiExceptionHandler)
    {
        $collectionPaymentDetail = PaymentDetails::getDetailsByCheckouId($request->get('checkout_id'));

        $gateway_aliases = ['dibs_easy_checkout',
            'nets_checkout',
            'easy_checkout',
            'dibs_easy_checkout_test',
            'nets_payment_d2_'];

        if(!in_array($request->get('gateway'), $gateway_aliases)) {
                     return response('Success', 200);
        }
        try{

            if( $collectionPaymentDetail->count() == 0) {
                return response('OK', 200);
            }
            $settingsCollection = MerchantSettings::getSettingsByShopOrigin($collectionPaymentDetail->first()->shop_url);
            $paymentId = $collectionPaymentDetail->first()->dibs_paymentid;
            if($collectionPaymentDetail->first()->test == 1) {
                $key = ShopifyApiService::decryptKey($settingsCollection->first()->easy_test_secret_key);
                $easyApiService->setEnv(EasyApiService::ENV_TEST);
            }else {
                $key = ShopifyApiService::decryptKey($settingsCollection->first()->easy_secret_key);
                $easyApiService->setEnv(EasyApiService::ENV_LIVE);
            }
            $easyApiService->setAuthorizationKey($key);
            $payment = $easyApiService->getPayment($paymentId);

            $shop = $settingsCollection->first()->shop_url;
            $accessToken = $settingsCollection->first()->access_token;
            $order = $shopifyApiService->getOrder($accessToken, $shop, $request->get('id'));
            $result = json_decode($order, true);

            /* Call shopify transaction api to create transaction for order status as paid in case of swish payments */
            if('authorized' == $result['order']['financial_status'] && $payment->getChargedAmount() != null) {
                $shopifyApiService->payTransaction($accessToken, $shop, $paymentId, $request->get('id'));
            }

            $jsonData = json_encode(['reference' => $request->get('name'),
                'checkoutUrl' => $payment->getCheckoutUrl()]);
            $easyApiService->updateReference($paymentId, $jsonData);
        }
        catch(ShopifyApiException $e) {
            $shopifyApiExceptionHandler->handle($e, $request->toArray());
            return response('HTTP/1.0 500 Internal Server Error', 500);
        }
        catch(\App\Exceptions\EasyException $e) {
            $eh->handle($e);
            return response('HTTP/1.0 500 Internal Server Error', 500);
        }
        catch(\Exception $e) {
            $handler->report($e);
            $logger->debug($request->all());
            return response('HTTP/1.0 500 Internal Server Error', 500);
        }
    }
}
