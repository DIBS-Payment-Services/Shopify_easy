<?php

namespace App\Http\Controllers\Accept;

use App\Exceptions\EasyApiExceptionHandler;
use App\Exceptions\ExceptionHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Service\ShopifyApiService;
use App\MerchantSettings;
use App\Service\EasyApiService;
use App\PaymentDetails;
use Illuminate\Log\Logger;

/**
 * Description of AcceptBase
 *
 * @author mabe
 */
class AcceptBase extends Controller {

    /**
     * @var Request
     */
    private $request;

    /**
     * @var EasyApiExceptionHandler
     */
    private $eh;

    /**
     * @var \App\ShopifyReturnParams
     */
    private $shopifyReturnParams;

    protected $easyApiService;

    protected $shopifyApiService;

    private $exHandler;

    private $logger;

    public function __construct(EasyApiService $easyApiService,
                                ShopifyApiService $shopifyApiService,
                                Request $request,
                                EasyApiExceptionHandler $eh,
                                ExceptionHandler $exHandler,
                                Logger $logger,
                                \App\ShopifyReturnParams $shopifyReturnParams) {
        $this->easyApiService = $easyApiService;
        $this->shopifyApiService = $shopifyApiService;
        $this->shopifyReturnParams = $shopifyReturnParams;
        $this->eh = $eh;
        $this->request = $request;
        $this->exHandler = $exHandler;
        $this->logger = $logger;
    }

    protected function handle() {
        try{
            $collectionPaymentDetail = PaymentDetails::getDetailsByCheckouId($this->request->get('checkout_id'));
            $checkoutTimeStart = $collectionPaymentDetail->first()->created_at;
			/* Commenting code to resolve order cancel issue due to easy payment timeout
            if($this->easyApiService->isPaymentTimeoutEnded($checkoutTimeStart)) {
                // redirect to cancel url
			  return redirect($this->request->get('x_url_cancel'));
			  
            }*/

            $keyField = static::KEY;
            $settingsCollection = MerchantSettings::getSettingsByShopOrigin($this->request->get('origin'));
            $this->easyApiService->setAuthorizationKey(ShopifyApiService::decryptKey($settingsCollection->first()->$keyField));
            $this->easyApiService->setEnv(static::ENV);
            $payment = $this->easyApiService->getPayment($collectionPaymentDetail->first()->dibs_paymentid);
            if(!empty($payment->getPaymentType())) {
                $this->shopifyReturnParams->setX_Amount($collectionPaymentDetail->first()->amount);
                $this->shopifyReturnParams->setX_Currency( $collectionPaymentDetail->first()->currency);
                $this->shopifyReturnParams->setX_GatewayReference($collectionPaymentDetail->first()->dibs_paymentid);
                $this->shopifyReturnParams->setX_Reference($collectionPaymentDetail->first()->checkout_id);
                $this->shopifyReturnParams->setX_Result('completed');
                $this->shopifyReturnParams->setX_Timestamp(date("Y-m-d\TH:i:s\Z"));
                $this->shopifyReturnParams->setX_TransactionType('authorization');
                $this->shopifyReturnParams->setX_AccountId($settingsCollection->first()->easy_merchantid);
                if($payment->getPaymentType() == 'CARD') {
                    $cardDetails = $payment->getCardDetails();
                    $this->shopifyReturnParams->setX_CardType($payment->getPaymentMethod());
                    $this->shopifyReturnParams->setX_CardMaskedPan($cardDetails['maskedPan']);
                }
                $this->shopifyReturnParams->setX_PaymentType($payment->getPaymentType());
                if($collectionPaymentDetail->first()->test == 1) {
                    $this->shopifyReturnParams->setX_Test();
                }
                $signature = $this->shopifyApiService->calculateSignature($this->shopifyReturnParams->getParams(), $settingsCollection->first()->gateway_password);
                $this->shopifyReturnParams->setX_Signature($signature);
                $params['params'] = $this->shopifyReturnParams->getParams();
                $params['url'] = $this->request->get('x_url_complete');
                return view('easy-accept', $params);
            } else {
                return redirect($this->request->get('x_url_cancel'));
            }
        } catch (\App\Exceptions\EasyException $e) {
              $this->eh->handle($e, $this->request->all());
              return response('HTTP/1.0 500 Internal Server Error', 500);
        } catch(\Exception $e) {
              $this->exHandler->report($e);
              return response('HTTP/1.0 500 Internal Server Error', 500);
        }
    }
}
