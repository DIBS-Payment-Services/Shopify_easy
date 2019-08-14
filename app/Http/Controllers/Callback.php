<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\ShopifyApiService;
use App\PaymentDetails;
use App\MerchantSettings;
use App\Service\EasyApiService;

class Callback extends Controller
{

    /**
     * @var \Illuminate\Log\Logger
     */
    private $logger;
    protected $shopifyApiService;
    private $shopifyApiExceptionHandler;

    public function __construct(ShopifyApiService $service, 
                                \App\Exceptions\ShopifyApiExceptionHandler $ehsh, 
                                \Illuminate\Log\Logger $logger) {
        $this->shopifyApiService = $service;
        $this->shopifyApiExceptionHandler = $ehsh;
        $this->logger = $logger;
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, \App\ShopifyReturnParams $shopifyReturnParams, EasyApiService $easyApiService)
    {
        try{
            error_log('callback_received12121');
         
            $data = $request->get('data');
            $shopifyReturnParams->setX_AccountId($request->get('merchantId'));
            $shopifyReturnParams->setX_Amount($data['amount']['amount'] / 100);
            $shopifyReturnParams->setX_Currency($data['amount']['currency']);
            $shopifyReturnParams->setX_GatewayReference($data['paymentId']);
            $shopifyReturnParams->setX_Reference($request->get('x_reference'));
            $shopifyReturnParams->setX_Result('completed');
            $shopifyReturnParams->setX_Timestamp(date("Y-m-d\TH:i:s\Z"));
            $shopifyReturnParams->setX_TransactionType('authorization');
            $pd = PaymentDetails::getDetailsByCheckouId($request->get('x_reference'));
            $ms = MerchantSettings::getSettingsByShopUrl($pd->first()->shop_url);
            $secret_key = '';
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
            $resultJson = json_decode($easyApiService->getPayment($data['paymentId']));
            $cardType = '';
            $maskedPan = '';
            if(!empty($resultJson->payment->paymentDetails) && !empty($resultJson->payment->paymentDetails->paymentType)) {
                if($resultJson->payment->paymentDetails->paymentType == 'CARD') {
                   if(!empty($resultJson->payment->paymentDetails->paymentMethod)) {
                       $cardType = $resultJson->payment->paymentDetails->paymentMethod;
                   }
                   if(!empty($resultJson->payment->paymentDetails->cardDetails->maskedPan)) {
                       $maskedPan = $resultJson->payment->paymentDetails->cardDetails->maskedPan;
                   }
                }
            }
            $shopifyReturnParams->setX_CardType($cardType);
            $shopifyReturnParams->setX_CardMaskedPan($maskedPan);
            $signature = $this->shopifyApiService->calculateSignature($shopifyReturnParams->getParams(), $ms->first()->gateway_password);
            $shopifyReturnParams->setX_Signature($signature);
            $this->shopifyApiService->paymentCallback($request->get('callback_url'), $shopifyReturnParams->getParams());
        }catch(\App\Exceptions\ShopifyApiException $e) {
            $this->shopifyApiExceptionHandler->handle($e);
            return response('HTTP/1.0 500 Internal Server Error', 500);
        } 
        catch( \Exception $e) {
            return response('HTTP/1.0 500 Internal Server Error', 500);
        }
    }
}
