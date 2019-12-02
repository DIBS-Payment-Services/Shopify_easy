<?php

namespace App\Service;
use App\DirectoryCountry;
use Illuminate\Http\Request;

/**
 * Description of EasyService
 *
 * @author mabe
 */
class EasyService implements EasyServiceInterface {


    private $request;
    private $logger;

    public function __construct(Request $request, \Illuminate\Log\Logger $logger) {
        $this->request = $request;
        $this->logger = $logger;
    }

    public function generateRequestParams(array $settings, \App\CheckoutObject $checkoutObject): array {
          $data = [
            'order' => [
                'items' => $this->getRequestObjectItems($checkoutObject, $settings['language']),
                'amount' => $checkoutObject->getAmount(),
                'currency' => $checkoutObject->getCurrency(),
                'reference' => $this->request->get('x_reference')],
             'checkout' => [
                    'termsUrl' => $settings['terms_and_conditions_url'],
                ],
           ];

            // b2b or b2bc 
            if(trim($settings['allowed_customer_type'])) {
                    switch($settings['allowed_customer_type']) {
                        case 'b2c' :
                            $supportedTypes = ['B2C'];
                            $default = 'B2C';
                        break;
                        case 'b2b':
                            $supportedTypes = ['B2B'];
                            $default = 'B2B';
                        break;
                        case 'b2c_b2b_b2c':
                            $supportedTypes = ['B2C', 'B2B'];
                            $default = 'B2C';
                        break;
                        case 'b2b_b2c_b2b':
                            $supportedTypes = ['B2C', 'B2B'];
                            $default = 'B2B';
                        break;
                        default:
                           $supportedTypes = ['B2C', 'B2B'];
                           $default = 'B2C';

               }
                   $consumerType = ['supportedTypes' => $supportedTypes,
                                    'default' => $default];
                   $data['checkout']['consumerType'] = $consumerType;
                   if('b2c' == $settings['allowed_customer_type']) {
                       // consumers data
                       if($this->customerAddressValidation($checkoutObject)) {
                           $firstName = ($checkoutObject->getCustomerFirstName()) ? $checkoutObject->getCustomerFirstName() : 'FirstName';
                           $lastName = ($checkoutObject->getcustomerLastName()) ? $checkoutObject->getcustomerLastName() : 'LastName';
                           $iso2countryCode = $checkoutObject->getIso2countryCode();
                           $res = DirectoryCountry::getCountry($iso2countryCode)->first();
                           $iso3countryCode = $res->iso3_code;
                           $consumerData = [
                                'email' => $checkoutObject->getCustomerEmail(),
                                 'shippingAddress' => [
                                            'addressLine1' =>  urlencode($checkoutObject->getAddressLine1()),
                                            'addressLine2' => urlencode($checkoutObject->getAddressLine2()),
                                            'postalCode' =>  $checkoutObject->getPostalCode(),
                                            'city' =>  urlencode($checkoutObject->getCity()),
                                            'country' =>  urlencode($iso3countryCode)],
                                 'privatePerson' => [
                                            'firstName' => $firstName,
                                            'lastName' => $lastName]
                            ];
                            $phone = null;
                            if(!empty($checkoutObject->getCustomerPhone())) {
                              $phone = $checkoutObject->getCustomerPhone();
                            } 
                            if(!empty($checkoutObject->getBillinAddresPhone())){
                               $phone = $checkoutObject->getBillinAddresPhone();
                            }
                            if(!empty($checkoutObject->getShippingAddresPhone())){
                               $phone = $checkoutObject->getShippingAddresPhone();
                            }
                            $phone = str_replace([' ', '-', '(', ')'], '', $phone);
                            if(preg_match('/\+[0-9]{7,18}$/', $phone) ) {
                               $phonePrefix = substr($phone, 0, 3);
                               $number = substr($phone, 3);
                               $consumerData['phoneNumber'] = ['prefix' => $phonePrefix, 'number' => $number];
                            }
                            $data['checkout']['consumer'] = $consumerData;
                            $data['checkout']['merchantHandlesConsumerData'] = true;
                       }
                   }
             }

             $x_url_complete = $this->request->get('x_url_complete');
             $url = ($this->request->get('x_test') == 'true') ? url('return_t')  : url('return');
             $data['checkout']['returnUrl'] = "$url?x_url_complete={$x_url_complete}";
             $data['checkout']['integrationType'] = 'HostedPaymentPage';
             $appUrl = env('SHOPIFY_APP_URL');
             $callbackUrl = $this->request->get('x_url_callback');
             $x_reference = $this->request->get('x_reference');
             $shop_url = $settings['shop_url'];
             $reservationCreatedurl = "https://{$appUrl}/callback?callback_url={$callbackUrl}&x_reference={$x_reference}&shop_url={$shop_url}";
             $chargeCreatedHookUrl = "https://{$appUrl}/charge_created?x_reference={$x_reference}";
             $refundCompletedWebhook = "https://{$appUrl}/refund_hook?x_reference={$x_reference}";
             $cancelCompletedWebhook = "https://{$appUrl}/cancel_hook?x_reference={$x_reference}";
             $data['notifications'] = 
                 ['webhooks' => 
                    [
                     ['eventName' => 'payment.checkout.completed',
                      'url' => $reservationCreatedurl,
                      'authorization' => substr(str_shuffle(MD5(microtime())), 0, 10)],

                     ['eventName' => 'payment.charge.created',
                      'url' => $chargeCreatedHookUrl,
                      'authorization' => substr(str_shuffle(MD5(microtime())), 0, 10)],

                     ['eventName' => 'payment.refund.completed',
                      'url' => $refundCompletedWebhook,
                      'authorization' => substr(str_shuffle(MD5(microtime())), 0, 10)],

                     ['eventName' => 'payment.cancel.created',
                      'url' => $cancelCompletedWebhook,
                      'authorization' => substr(str_shuffle(MD5(microtime())), 0, 10)]]];
             return $data;
    }

   /**
    * 
    * @param type $checkout
    * @return type
    */
   public function getRequestObjectItems(\App\CheckoutObject $checkoutObject, $lang = 'en-US') {
            $items = [];

            // Products
            foreach ($checkoutObject->getLineItems() as $item) {
               if($checkoutObject->isTaxesIncluded()) {
                    $unitPrice =  round($item['price'] / (1 + $this->getTaxRate($item)) * 100);
                    $taxRate =  round($this->getTaxRate($item) * 10000);
                    $taxAmount = round($this->getTaxPrice($item) * 100);
                    $grossTotalAmount = round($item['price'] * 100) * $item['quantity'];
                    $netTotalAmount =  round($item['price'] *  $item['quantity'] / (1 + $this->getTaxRate($item)) * 100);
               } else {
                    $unitPrice =  round($item['price'] * 100);
                    $taxRate =  0;
                    $taxAmount = $checkoutObject->getTotalTax() * 100;
                    $grossTotalAmount = round(($item['price'] * 100)) * $item['quantity'];
                    $netTotalAmount =  round($item['price'] *  $item['quantity'] * 100);

               }
               $items[] = array(
                    'reference' => !empty($item['product_id']) ? $item['product_id']: md5($item['title']),
                    'name' => str_replace(array('\'', '&'), '', $this->trimProductName($item['title'])),
                    'quantity' => $item['quantity'],
                    'unit' => 'pcs',
                    'unitPrice' => $unitPrice,
                    'taxRate' => $taxRate,
                    'taxAmount' => $taxAmount,
                    'grossTotalAmount' => $grossTotalAmount,
                    'netTotalAmount' => $netTotalAmount);
            }

            //Shipping
            if($shippingLine = $this->getShippingLine($checkoutObject)) {
                $items[] = $shippingLine;
            }

            //Discount
            if($this->getDiscountAmount($checkoutObject) > 0) {
                $items[] = $this->discountRow($this->getDiscountAmount($checkoutObject));
            }

            if(!$checkoutObject->isTaxesIncluded()) {
                 $items[] = $this->taxRow($checkoutObject->getTotalTax(), $lang);
            }

            return $items;
    }

    public function getShippingLine(\App\CheckoutObject $checkoutObject) {
        $shipping = [];
        if(!empty(($checkoutObject->getShippingLines()))) {
               $current = current($checkoutObject->getShippingLines());
            if($checkoutObject->isTaxesIncluded()) {
                $unitPrice = round($current['price'] / (1 + $this->getTaxRate($current)) * 100);
                $taxRate =  round($this->getTaxRate($current) * 10000);
                $taxAmount = round($this->getTaxPrice($current) * 100);
                $grossTotalAmount = round($current['price'] * 100);
                $netTotalAmount =  round($current['price'] / (1 + $this->getTaxRate($current)) * 100);
            } else {
                 $unitPrice = round($current['price'] * 100);
                 $taxRate =  0;
                 $taxAmount = 0;
                 $grossTotalAmount = round(($current['price'] ) * 100);
                 $netTotalAmount =  round($current['price'] *  100);
            }
            $shippingLine =  [
                    'reference' => !empty($current['id']) ? $current['id'] : md5($current['title']),
                    'name' => str_replace(array('\'', '&'), '', $this->trimProductName($current['title'])),
                    'quantity' => 1,
                    'unit' => 'pcs',
                    'unitPrice' => $unitPrice,
                    'taxRate' => $taxRate,
                    'taxAmount' => $taxAmount,
                    'grossTotalAmount' => $grossTotalAmount,
                    'netTotalAmount' => $netTotalAmount];
            
            return $shippingLine;
            
        }
    }

    protected function getTaxPrice($item) {
        $price = 0;
        foreach($item['tax_lines'] as $tax) {
                $price += $tax['price'];
            }
        return $price;
    }

    protected function getTaxRate($item) {
        $rate = 0;
        foreach($item['tax_lines'] as $tax) {
                $rate += $tax['rate'];
            }
        return $rate;
    }

    protected function getDiscountAmount(\App\CheckoutObject $checkoutObject) {
        $amount = 0;
        if(!empty($checkoutObject->getTotalDiscounts())) {
            $amount = $checkoutObject->getTotalDiscounts();
        }
        return $amount;
    }

    protected function discountRow($amount) {
        return [
                'reference' => 'discount',
                'name' => str_replace(array('\'', '&'), '', 'Discount'),
                'quantity' => 1,
                'unit' => 'pcs',
                'unitPrice' => -round($amount * 100),
                'taxRate' => 0,
                'taxAmount' => 0,
                'grossTotalAmount' => -round($amount * 100),
                'netTotalAmount' => -round($amount * 100)];
    }
    
    protected function taxRow($amount, $lang = 'en-GB') {
        return [
                'reference' => 'tax',
                'name' => $this->getTaxTranslation($lang),
                'quantity' => 1,
                'unit' => 'pcs',
                'unitPrice' => round($amount * 100),
                'taxRate' => 0,
                'taxAmount' => 0,
                'grossTotalAmount' => round($amount * 100),
                'netTotalAmount' => round($amount * 100)];
    }
    
    public function getFakeOrderRow($amount, $name) {
         return [
                'reference' => md5($amount . $name),
                'name' => $name,
                'quantity' => 1,
                'unit' => 'pcs',
                'unitPrice' => $amount,
                'taxRate' => 0,
                'taxAmount' => 0,
                'grossTotalAmount' => $amount,
                'netTotalAmount' => $amount];
    }

    protected function trimProductName(string $productName) {
        return substr($productName, 0, 128);
    }

    /**
     *
     * @param \App\CheckoutObject $checkoutObject
     * @return boolean
     */
    protected function customerAddressValidation(\App\CheckoutObject $checkoutObject) {
        if(!empty($checkoutObject->getIso2countryCode())  &&
           !empty($checkoutObject->getPostalCode())  &&
           (!empty( $checkoutObject->getAddressLine1()) ||
           !empty($checkoutObject->getAddressLine2()))) {
              return true;
          } else {
              return false;
          }
    }

    public static function formatEasyAmount($amount) {
        return (int) ( round($amount *100) );
    }

    private function getTaxTranslation($language = 'en-GB') {
        $result = 'Tax';
        switch ($language) {
            case 'en-GB':
               $result = 'VAT';
            break;
            case 'nb-NO':
                $result = 'MVA';
            break;
            case 'sv-SE':
                $result = 'MOMS';
            break;
            case 'da-DK':
                $result = 'MOMS';
            break;
        }
        return $result;
    }
}
