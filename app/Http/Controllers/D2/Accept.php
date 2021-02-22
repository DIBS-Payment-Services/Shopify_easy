<?php


namespace App\Http\Controllers\D2;

use App\Http\Controllers\Controller;
use App\Service\ShopifyApiService;
use App\ShopifyReturnParams;
use Illuminate\Http\Request;

class Accept extends Controller
{

    /**
     * @var ShopifyReturnParams
     */
    private $shopifyReturnParams;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var ShopifyApiService
     */
    private $shopifyApiService;

    /**
     * Accept constructor.
     * @param ShopifyReturnParams $shopifyReturnParams
     * @param ShopifyApiService $shopifyApiService
     * @param Request $request
     */
    public function __construct(ShopifyReturnParams $shopifyReturnParams,
                                ShopifyApiService $shopifyApiService, Request $request) {
        $this->shopifyReturnParams = $shopifyReturnParams;
        $this->request = $request;
        $this->shopifyApiService = $shopifyApiService;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function __invoke() {
       $this->shopifyReturnParams->setX_Amount($this->request->get('x_amount'));
       $this->shopifyReturnParams->setX_Currency($this->request->get('currency'));
       $this->shopifyReturnParams->setX_GatewayReference($this->request->get('transact'));
       $this->shopifyReturnParams->setX_Reference($this->request->get('orderid'));
       $this->shopifyReturnParams->setX_Result('completed');
       $this->shopifyReturnParams->setX_Timestamp(date("Y-m-d\TH:i:s\Z"));
       $this->shopifyReturnParams->setX_TransactionType('authorization');
       $this->shopifyReturnParams->setX_AccountId($this->request->get('merchantId'));
       $this->shopifyReturnParams->setX_PaymentType($this->request->get('paytype'));

       if(1 == $this->request->get('test')) {
           $this->shopifyReturnParams->setX_Test();
       }
       $password = env('D2_NETS_GATEWAY_PASSWORD');
       $signature = $this->shopifyApiService->calculateSignature($this->shopifyReturnParams->getParams(), $password);
       $this->shopifyReturnParams->setX_Signature($signature);
       $params['url'] = $this->request->get('x_url_complete');
       $params['params'] = $this->shopifyReturnParams->getParams();
       $params['method'] = 'GET';
       return view('d2-redirect-form', $params);

    }
}
