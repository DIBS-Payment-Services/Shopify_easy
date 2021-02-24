<?php

namespace App\Http\Controllers\Refund;

use App\Exceptions\EasyApiExceptionHandler;
use App\Exceptions\Handler;
use App\Exceptions\ShopifyApiExceptionHandler;
use App\Http\Controllers\Controller;
use App\ShopifyReturnParams;
use Illuminate\Http\Request;
use App\Service\ShopifyApiService;
use App\MerchantSettings;
use App\Service\EasyService;
use App\Service\EasyApiService;
use App\CheckoutObject;
use App\PaymentDetails;
use Illuminate\Log\Logger;

class RefundBase extends Controller
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

    /**
     * @var Request
     */
    private $request;

    /**
     * @var EasyService
     */
    private $easyService;

    /**
     * @var EasyApiExceptionHandler
     */
    private $eh;

    /**
     * @var ShopifyApiExceptionHandler
     */
    private $ehsh;

    /**
     * @var Handler
     */
    private $handler;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ShopifyReturnParams
     */
    private $shopifyReturnParams;

    /**
     * RefundBase constructor.
     * @param Request $request
     * @param EasyService $easyService
     * @param EasyApiExceptionHandler $eh
     * @param ShopifyApiExceptionHandler $ehsh
     * @param Handler $handler
     * @param CheckoutObject $checkoutObject
     * @param EasyApiService $easyApiService
     * @param ShopifyApiService $shopifyApiService
     * @param Logger $logger
     * @param ShopifyReturnParams $shopifyReturnParams
     */
    public function __construct(Request $request,
                                EasyService $easyService,
                                EasyApiExceptionHandler $eh,
                                ShopifyApiExceptionHandler $ehsh,
                                Handler $handler,
                                CheckoutObject $checkoutObject,
                                EasyApiService $easyApiService,
                                ShopifyApiService $shopifyApiService,
                                Logger $logger,
                                ShopifyReturnParams $shopifyReturnParams
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
        $this->shopifyReturnParams = $shopifyReturnParams;
    }

   protected function handle() {
          try{
             if('D2' == $this->easyApiService->detectMerchantType($this->request->get('x_account_id'))) {
                 $params = $this->request->all();
                 $orderid = $this->easyApiService->getD2Payment($this->request->get('x_account_id'), $this->request->get('x_gateway_reference'));

                 $data = array('merchant' => $this->request->get('x_account_id'),
                     'amount'   => $this->request->get('x_amount') * 100,
                     'transact' => $this->request->get('x_gateway_reference'),
                     'orderid'  => $orderid,
                     'currency' => $this->request->get('x_currency'),
                     'textreply' => 'yes');

                 $this->easyApiService->refundPaymentD2($this->request->get('x_account_id'), $data);
                 $this->flushHeader();
                 sleep(30);
                 $this->shopifyReturnParams->setX_Amount($params['x_amount']);
                 $this->shopifyReturnParams->setX_GatewayReference($params['x_gateway_reference']);
                 $this->shopifyReturnParams->setX_Reference($params['x_reference']);
                 $this->shopifyReturnParams->setX_Result('completed');
                 $this->shopifyReturnParams->setX_Timestamp(date("Y-m-d\TH:i:s\Z"));
                 $this->shopifyReturnParams->setX_TransactionType('refund');
                 $pass = env('D2_NETS_GATEWAY_PASSWORD');
                 $this->shopifyReturnParams->setX_Signature($this->shopifyApiService->calculateSignature($this->shopifyReturnParams->getParams(),$pass));
                 $this->shopifyApiService->paymentCallback($params['x_url_callback'], $this->shopifyReturnParams->getParams());
             }else {
                  $paymentDetails = PaymentDetails::getDetailsByPaymentId($this->request->get('x_gateway_reference'));
                  $settingsCollection = MerchantSettings::getSettingsByShopOrigin($paymentDetails->first()->shop_url);
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
              }
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

    protected function flushHeader() {
        ob_start();
        echo "OK";
        $size = ob_get_length();
        header("Content-Encoding: none");
        header("Content-Length: {$size}");
        header("Connection: close");
        ob_end_flush();
        ob_flush();
        flush();
    }

}
