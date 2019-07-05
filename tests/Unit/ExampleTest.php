<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Service\ShopifyApiService;

use App\MerchantSettings;

use App\Service\EasyApiService;

class ExampleTest extends TestCase
{
    
    const TRANSACTION_ID = '04c9fc2439d047f580459b6ef0fe0338';
    
    
    protected $shopifyService;
    
     /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp() :void {
        
       parent::setUp();
       $this->shopifyService = $this->app->make('App\Service\EasyApiService');
       $merchantSettings = $this->app->make('App\MerchantSettings');
    }
    
    public function testEasyGetPayment() {
       $url = EasyApiService::GET_PAYMENT_DETAILS_URL_TEST_PREFIX . self::TRANSACTION_ID;
       $tst = MerchantSettings::getSettingsByShopName('dibeasysek');
       $key = ShopifyApiService::decryptKey($tst->first()->easy_test_secret_key);
       $result = $this->shopifyService->getPayment($key, $url);
       error_log($result);
       $this->assertTrue(is_string($result));
    }
}
