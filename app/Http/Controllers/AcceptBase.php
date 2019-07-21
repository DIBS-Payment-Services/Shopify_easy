<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\ShopifyApiService;
use App\MerchantSettings;
use App\Service\EasyApiService;


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
     * @var \App\Exceptions\EasyApiExceptionHandler
     */
    private $eh;

    /**
     * @var \App\ShopifyReturnParams
     */
    private $shopifyReturnParams;
    protected $easyApiService;
    protected $shopifyApiService;
    
    public function __construct(EasyApiService $easyApiService, 
                                ShopifyApiService $shopifyApiService, 
                                Request $request, \Illuminate\Log\Logger $logger, 
                                \App\Service\Api\Client $client, 
                                \App\Exceptions\EasyApiExceptionHandler $eh,
                                \App\ShopifyReturnParams $shopifyReturnParams) {
        $this->easyApiService = $easyApiService;
        $this->shopifyApiService = $shopifyApiService;
        $this->shopifyReturnParams = $shopifyReturnParams;
        $this->eh = $eh;
        $this->request = $request;
    }

    protected function handle() {
        try{
            $requestInitialParams = json_decode(session('request_params'), true);
            $settingsCollection = MerchantSettings::getSettingsByShopUrl($requestInitialParams['shop']);
            
            $keyField = static::KEY;
            $key = ShopifyApiService::decryptKey($settingsCollection->first()->$keyField);
                        
            $this->easyApiService->setAuthorizationKey($key);
            $this->easyApiService->setEnv(static::ENV);
            $paymentDetailsJson = $this->easyApiService->getPayment($requestInitialParams['paymentId']);
            
            $paymentDetailsList = json_decode($paymentDetailsJson);
            
            if(!empty($paymentDetailsList->payment->summary->reservedAmount)) {
                $params['url'] = $this->request->get('x_url_complete'); 
                $requestInitialParams = json_decode(session('request_params'), true);
                $this->shopifyReturnParams->setX_Amount($requestInitialParams['x_amount']);
                $this->shopifyReturnParams->setX_Currency( $requestInitialParams['x_currency']);
                $this->shopifyReturnParams->setX_GatewayReference($requestInitialParams['paymentId']);
                $this->shopifyReturnParams->setX_Reference($requestInitialParams['x_reference']);
                $this->shopifyReturnParams->setX_Result('completed');
                $this->shopifyReturnParams->setX_Timestamp(date("Y-m-d\TH:i:s\Z"));
                $this->shopifyReturnParams->setX_TransactionType('authorization');
                $this->shopifyReturnParams->setX_AccountId($requestInitialParams['x_account_id']);

                if($requestInitialParams['x_test'] == 'true') {
                    $this->shopifyReturnParams->setX_Test();
                }
                $pass = $requestInitialParams['gateway_password'];
                $signature = $this->shopifyApiService->calculateSignature($this->shopifyReturnParams->getParams(), $pass);
                $this->shopifyReturnParams->setX_Signature($signature);
                $params['params'] = $this->shopifyReturnParams->getParams();
                return view('easy-accept', $params);
            } else {
                return redirect($requestInitialParams['x_url_cancel']);
            }
        } catch (\App\Exceptions\EasyException $e) {
              $this->eh->handle($e, $this->request->all());
        } catch(\Exception $e) {
              echo $e->getMessage();
              return response('HTTP/1.0 500 Internal Server Error', 500);
        }
    }
}
