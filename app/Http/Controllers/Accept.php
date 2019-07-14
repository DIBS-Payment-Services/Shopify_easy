<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\ShopifyApiService;
use App\MerchantSettings;
use App\Service\EasyApiService;

use \Monolog\Logger;

class Accept extends Controller
{
    protected $easyApiService;
    protected $shopifyApiService;
    
    public function __construct(EasyApiService $easyApiService, ShopifyApiService $shopifyApiService) {
        $this->easyApiService = $easyApiService;
        $this->shopifyApiService = $shopifyApiService;
    }
 
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, \Illuminate\Log\Logger $logger, 
                             \App\Service\Api\Client $client, 
                             \App\Exceptions\EasyApiExceptionHandler $eh,
                             \App\ShopifyReturnParams $shopifyReturnParams)
    {
        try{
            $requestInitialParams = json_decode(session('request_params'), true);
            $settingsCollection = MerchantSettings::getSettingsByShopUrl($requestInitialParams['shop']);
            if($requestInitialParams['x_test'] == 'true') {
                $key = ShopifyApiService::decryptKey($settingsCollection->first()->easy_test_secret_key);
                $env = EasyApiService::ENV_TEST;
                        
            } else {
                $env = EasyApiService::ENV_LIVE;
                $key = ShopifyApiService::decryptKey($settingsCollection->first()->easy_secret_key);
            }
            
            $this->easyApiService->setAuthorizationKey($key);
            $this->easyApiService->setEnv($env);
            $paymentDetailsJson = $this->easyApiService->getPayment($requestInitialParams['paymentId']);
            
            $paymentDetailsList = json_decode($paymentDetailsJson);
            if(!empty($paymentDetailsList->payment->summary->reservedAmount)) {
                $params['url'] = $request->get('x_url_complete'); 

                $requestInitialParams = json_decode(session('request_params'), true);
                $shopifyReturnParams->setX_Amount($requestInitialParams['x_amount']);
                $shopifyReturnParams->setX_Currency( $requestInitialParams['x_currency']);
                $shopifyReturnParams->setX_GatewayReference($requestInitialParams['paymentId']);
                $shopifyReturnParams->setX_Reference($requestInitialParams['x_reference']);
                $shopifyReturnParams->setX_Result('completed');
                $shopifyReturnParams->setX_Timestamp(date("Y-m-d\TH:i:s\Z"));
                $shopifyReturnParams->setX_TransactionType('authorization');
                $shopifyReturnParams->setX_AccountId($requestInitialParams['x_account_id']);

                if($requestInitialParams['x_test'] == 'true') {
                    $shopifyReturnParams->setX_Test();
                }
                $pass = $requestInitialParams['gateway_password'];
                $signature = $this->shopifyApiService->calculateSignature($shopifyReturnParams->getParams(), $pass);
                $shopifyReturnParams->setX_Signature($signature);
                $params['params'] = $shopifyReturnParams->getParams();
                return view('easy-accept', $params);


            } else {
                return redirect($requestInitialParams['x_url_cancel']);
            }
        } catch (\App\Exceptions\EasyException $e) {
              $eh->handle($e, $request->all());
        } catch(\Exception $e) {
              return response('HTTP/1.0 500 Internal Server Error', 500);
        }
    }
  
}
