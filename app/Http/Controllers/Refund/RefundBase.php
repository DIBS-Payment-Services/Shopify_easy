<?php

namespace App\Http\Controllers\Refund;

use Illuminate\Http\Request;

use App\Service\ShopifyApiService;
use App\MerchantSettings;
use App\Service\EasyService;
use App\Service\EasyApiService;
use App\CheckoutObject;
use App\PaymentDetails;

class RefundBase extends \App\Http\Controllers\Controller
{
     /**
     * @var ShopifyApiService
     */
    private $shopifyApiService;

    /**
     * @var EasyApiService
     */
    private $easyApiService;

    /**
     * @var CheckoutObject
     */
    private $checkoutObject;
    private $request;
    private $easyService;
    private $eh;
    private $ehsh;
    private $handler;
    private $logger;

    public function __construct(Request $request,  
                                EasyService $easyService,
                                \App\Exceptions\EasyApiExceptionHandler $eh,
                                \App\Exceptions\ShopifyApiExceptionHandler $ehsh,
                                \App\Exceptions\Handler $handler,
                                CheckoutObject $checkoutObject,
                                EasyApiService $easyApiService,
                                ShopifyApiService $shopifyApiService,
                                \Illuminate\Log\Logger $logger
            ) {
        $this->request = $request;
        $this->easyService = $easyService;
        $this->eh = $eh;
        $this->ehsh= $ehsh;
        $this->handler = $handler;
        $this->checkoutObject = $checkoutObject;
        $this->easyApiService = $easyApiService;
        $this->checkoutObject = $checkoutObject;
        $this->easyApiService = $easyApiService;
        $this->shopifyApiService = $shopifyApiService;
        $this->logger = $logger;
    }

   protected function handle() {
          try{
             $paymentDetails = PaymentDetails::getDetailsByPaymentId($this->request->get('x_gateway_reference'));
             $settingsCollection = MerchantSettings::getSettingsByShopUrl($paymentDetails->first()->shop_url);
             $params = $this->request->all();
             $params['x_test'] = (static::ENV == 'live') ? 'false' : 'true';
             $fieldName = static::KEY; 
             $key = ShopifyApiService::decryptKey($settingsCollection->first()->$fieldName);
             $this->easyApiService->setEnv(static::ENV);
             $this->easyApiService->setAuthorizationKey($key);
             unset($params['x_signature']);
             $gatewayPassword = $settingsCollection->first()->gateway_password;
             if($this->request->get('x_signature') != $this->shopifyApiService->calculateSignature($params, $gatewayPassword)) {
                throw new \App\Exceptions\ShopifyApiException('Singnature is wrong while trying to capture');
             }
             $orderJson = $this->shopifyApiService->getOrder($settingsCollection->first()->access_token, 
             $settingsCollection->first()->shop_url, $this->request->get('x_shopify_order_id'));
             $orderDecoded = json_decode($orderJson, true);
             $this->checkoutObject->setCheckout($orderDecoded['order']);
             PaymentDetails::persistRefundRequestParams($orderDecoded['order']['checkout_id'], json_encode($this->request->all()));
             if($this->easyService::formatEasyAmount($this->request->get('x_amount')) == $this->checkoutObject->getAmount()) {
                 $data['amount'] = $this->checkoutObject->getAmount();
                 $data['orderItems'] = json_decode($paymentDetails->first()->create_payment_items_params, true);
             } else {
                 $data['amount'] = $this->easyService::formatEasyAmount($this->request->get('x_amount'));
                 $data['orderItems'][] = $this->easyService->getFakeOrderRow($this->easyService::formatEasyAmount($this->request->get('x_amount')), 'refunded-partially');
             }
             $payment = $this->easyApiService->getPayment($this->request->get('x_gateway_reference'));
             $this->easyApiService->refundPayment($payment->getFirstChargeId(), json_encode($data));
         } catch(\App\Exceptions\ShopifyApiException $e) {
            $this->ehsh->handle($e, $this->request->all());
            return response('HTTP/1.0 500 Internal Server Error', 500);
         } catch(\App\Exceptions\EasyException $e) {
            $this->eh->handle($e);
            return response('HTTP/1.0 500 Internal Server Error', 500);
         }
         catch(\Exception $e) {
            $this->handler->report($e);
            $this->logger->debug($this->request->all());
            return response('HTTP/1.0 500 Internal Server Error', 500);
         }
    }

}
