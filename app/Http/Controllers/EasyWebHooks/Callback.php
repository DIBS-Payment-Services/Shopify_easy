<?php

namespace App\Http\Controllers\EasyWebHooks;

use App\Exceptions\ShopifyApiException;
use App\Exceptions\ShopifyApiExceptionHandler;
use App\Http\Controllers\Controller;
use App\ShopifyReturnParams;
use Illuminate\Http\Request;
use App\Service\ShopifyApiService;
use App\PaymentDetails;
use App\MerchantSettings;
use App\Service\EasyApiService;
use Illuminate\Http\Response;
use Illuminate\Log\Logger;

class Callback extends Controller
{

    /**
     * @var Logger
     */
    private $logger;
    protected $shopifyApiService;
    private $shopifyApiExceptionHandler;

    public function __construct(ShopifyApiService $service,
                                ShopifyApiExceptionHandler $ehsh,
                                Logger $logger) {
        $this->shopifyApiService = $service;
        $this->shopifyApiExceptionHandler = $ehsh;
        $this->logger = $logger;
    }

    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @param ShopifyReturnParams $shopifyReturnParams
     * @param EasyApiService $easyApiService
     * @return Response
     */
    public function __invoke(Request $request,
                             ShopifyReturnParams $shopifyReturnParams,
                             EasyApiService $easyApiService)
    {
        try{
            $data = $request->get('data');
            $shopifyReturnParams->setX_AccountId($request->get('merchantId'));
            $shopifyReturnParams->setX_Amount($data['order']['amount']['amount'] / 100);
            $shopifyReturnParams->setX_Currency($data['order']['amount']['currency']);
            $shopifyReturnParams->setX_GatewayReference($data['paymentId']);
            $shopifyReturnParams->setX_Reference($request->get('x_reference'));
            $shopifyReturnParams->setX_Result('completed');
            $shopifyReturnParams->setX_Timestamp(date("Y-m-d\TH:i:s\Z"));
            $shopifyReturnParams->setX_TransactionType('authorization');
            $pd = PaymentDetails::getDetailsByCheckouId($request->get('x_reference'));
            $ms = MerchantSettings::getSettingsByShopOrigin($pd->first()->shop_url);
            if($pd->first()->test == 1) {
                $shopifyReturnParams->setX_Test();
                $secret_key = $ms->first()->easy_test_secret_key;
                $easyApiService->setEnv('test');
            }else {
                 $secret_key = $ms->first()->easy_secret_key;
                 $easyApiService->setEnv('live');
            }
            $secret_key = $this->shopifyApiService->decryptKey($secret_key);
            $easyApiService->setAuthorizationKey($secret_key);
            $payment = $easyApiService->getPayment($data['paymentId']);
            $shopifyReturnParams->setX_PaymentType($payment->getPaymentType());
            if( $payment->getPaymentType() == 'CARD') {
                   $cardDetails = $payment->getCardDetails();
                   $shopifyReturnParams->setX_CardType($payment->getPaymentMethod());
                   $shopifyReturnParams->setX_CardMaskedPan($cardDetails['maskedPan']);
            }
            $signature = $this->shopifyApiService->calculateSignature($shopifyReturnParams->getParams(), $ms->first()->gateway_password);
            $shopifyReturnParams->setX_Signature($signature);
            $this->shopifyApiService->paymentCallback($request->get('callback_url'), $shopifyReturnParams->getParams());
        }catch(ShopifyApiException $e) {
            $this->shopifyApiExceptionHandler->handle($e);
            return response('HTTP/1.0 500 Internal Server Error', 500);
        }
        catch(\Exception $e) {
           return response('HTTP/1.0 500 Internal Server Error', 500);
        }
    }
}
