<?php

namespace App\Service;
use App\DirectoryCountry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
                'amount' => $this->getAmount(),
                'currency' => $checkoutObject->getCurrency(),
                'reference' => $this->request->get('x_reference')],
             'checkout' => [
                    'termsUrl' => $settings['terms_and_conditions_url'],
                ],
           ];

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
                                'country' =>  urlencode($iso3countryCode)]];
            
                if($checkoutObject->getCompany()) {
                 $consumerData['company'] =  ['name'=> 'DIBS', 
                                             'contact' => ['firstName' => 'Test', 
                                              'lastName' => 'Dibs']]; 
                 }else {
                    $consumerData['privatePerson'] = ['firstName' => $firstName,
                                         'lastName' => $lastName]; 
                }

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
             }
                // don't show address form in Easy Window
                $data['checkout']['merchantHandlesConsumerData'] = true;

             $x_url_complete = $this->request->get('x_url_complete');
             $url = ($this->request->get('x_test') == 'true') ? url('return_t')  : url('return');
             $shop_url = $settings['shop_url'];
             $x_reference = $this->request->get('x_reference');
             $x_url_cancel = $this->request->get('x_url_cancel');
             $data['checkout']['returnUrl'] = "$url?x_url_complete={$x_url_complete}&origin={$shop_url}&checkout_id={$x_reference}&x_url_cancel={$x_url_cancel}";
             $data['checkout']['integrationType'] = 'HostedPaymentPage';
             $appUrl = env('SHOPIFY_APP_URL');
             $callbackUrl = $this->request->get('x_url_callback');
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
                    $taxAmount = round($checkoutObject->getTotalTax() * 100);
                    $grossTotalAmount = round(($item['price'] * 100)) * $item['quantity'];
                    $netTotalAmount =  round($item['price'] *  $item['quantity'] * 100);

               }
               $items[] = array(
                    'reference' => !empty($item['product_id']) ? $item['product_id']: md5($item['title']),
                    'name' => strip_tags(str_replace(array('\'', '&'), '', $this->trimProductName($item['title']))),
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

            // it is not possible to fetch Gift discount amount from Checkout object
            // so we can only do this way...
            $deltaAmount = $checkoutObject->getAmount() - $this->getAmount();
            if($deltaAmount > 0) {
               $items[] = $this->giftDiscount($deltaAmount);
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
                 $grossTotalAmount = round(($current['price']) * 100);
                 $netTotalAmount =  round($current['price'] *  100);
            }
            $shippingLine =  [
                    'reference' => !empty($current['id']) ? $current['id'] : md5($current['title']),
                    'name' => strip_tags(str_replace(array('\'', '&'), '', $this->trimProductName($current['title']))),
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

    protected function getAmount() {
        return (int)round($this->request->get('x_amount') * 100);
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

    protected function giftDiscount($amount, $lang = 'en-GB') {
        return [
                'reference' => 'gift-discount',
                'name' => 'Discount',
                'quantity' => 1,
                'unit' => 'pcs',
                'unitPrice' => -$amount,
                'taxRate' => 0,
                'taxAmount' => 0,
                'grossTotalAmount' => -$amount,
                'netTotalAmount' => -$amount];
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
        $email =  $checkoutObject->getCustomerEmail();
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
             return false;
        }
       if(!empty($checkoutObject->getIso2countryCode())  &&
           !empty($checkoutObject->getPostalCode())  &&
           (!empty( $checkoutObject->getAddressLine1()) ||
           !empty($checkoutObject->getAddressLine2())))
          {
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

    public function getFakeChekout($request) {
       return ['total_price' => $request->get('x_amount'),
               'presentment_currency' => $request->get('x_currency'),
               'shipping_address' =>
                    ['country_code' =>  $request->get('x_customer_billing_country'),
                     'phone' => $request->get('x_customer_shipping_phone'),
                     'address1' => $request->get('x_customer_shipping_address1'),
                     'address2' => $request->get('x_customer_shipping_address2'),
                     'zip'  => $request->get('x_customer_shipping_zip'),
                     'city' => $request->get('x_customer_shipping_city'),
                     'company' => $request->get('x_customer_shipping_company')],
               'billing_address' =>
                    ['country_code' =>  $request->get('x_customer_billing_country'),
                     'phone' => $request->get('x_customer_billing_phone'),
                     'address1' => $request->get('x_customer_billing_address1'),
                     'address2' => $request->get('x_customer_billing_address2'),
                     'zip' => $request->get('x_customer_billing_zip'),
                     'city' => $request->get('x_customer_billing_company')],
               'customer' =>
                    ['email' => $request->get('x_customer_email'),
                     'first_name' => $request->get('x_customer_first_name'),
                     'last_name' => $request->get('x_customer_last_name')],
               'line_items' =>
                    [['price' => $request->get('x_amount'),
                      'quantity' => 1,
                      'product_id' => 'prd12',
                      'title' => 'Product1']],
               'taxes_included' => null,
               'total_tax' => null,
               'shipping_lines' => null,
               'total_discounts' => null,
               'token' => rand(1, 100500),
               'id' => $request->get('x_reference')
          ];
    }
}
