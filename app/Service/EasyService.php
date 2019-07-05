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

    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function generateRequestParams($settings, $checkout = []): array {
            $data = array(
            'order' => array(
                'items' => $this->getRequestObjectItems($checkout),
                'amount' => round($checkout['total_price'] * 100),
                'currency' => $checkout['presentment_currency'],
                'reference' => $this->request->get('x_reference')),
             'checkout' => array(
                    'termsUrl' => $settings['terms_and_conditions_url'],
                ),
            );
          $iso2countryCode = isset($checkout['shipping_address']['country_code']) ? $checkout['shipping_address']['country_code'] : $checkout['billing_address']['country_code'];
          
          
          error_log($iso2countryCode);
          $res = DirectoryCountry::getCountry($iso2countryCode)->first();
          $iso3countryCode = $res->iso3_code;

          $phone = null;
          if(!empty($checkout['customer']['phone'])) {
              $phone= $checkout['customer']['phone'];
          } 
          if(!empty($checkout['billing_address']['phone'])){
               $phone= $checkout['billing_address']['phone'];
          }
          if(!empty($checkout['shipping_address']['phone'])){
               $phone= $checkout['shipping_address']['phone'];
          }
          $phone = str_replace([' ', '-', '(', ')'], '', $phone);
          if(!preg_match('/^\+[0-9]*/', $phone)) {
              if(preg_match('/^[0-9]{5,15}/', $phone)) {
                  $prefix = null;
                  switch($iso3countryCode) {
                      case 'SWE':
                          $prefix= '+46';
                          break;
                      case 'DNK':
                           $prefix= '+45';
                          break;
                      case 'NOR':
                          $prefix= '+47';
                          break;

                  }
                  if($prefix) {
                    $phone = $prefix . $phone;
                  }
              } else {
                  unset($phone);
              }
          } 
          if(!empty($phone)) {
               $phonePrefix = substr($phone, 0, 3);
               $number = substr($phone, 3);
               $data['checkout']['consumer'] = array(
                            'email' => $checkout['customer']['email'],
                            "shippingAddress" => array(
                                "addressLine1"=>  isset($checkout['shipping_address']['address1']) ? $checkout['shipping_address']['address1'] : $checkout['billing_address']['address1'],
                                "addressLine2"=>  isset($checkout['shipping_address']['address2']) ? $checkout['shipping_address']['address2'] : $checkout['billing_address']['address2'],
                                "postalCode"=>  isset($checkout['shipping_address']['zip']) ? $checkout['shipping_address']['zip'] : $checkout['billing_address']['zip'] ,
                                "city"=>  isset($checkout['shipping_address']['city']) ? $checkout['shipping_address']['city'] : $checkout['billing_address']['city'],
                                "country"=>  $iso3countryCode,
                              ),
                          'phoneNumber' => ['prefix' => $phonePrefix,   'number' => $number],
                          'privatePerson' => array(
                                'firstName' => $checkout['customer']['first_name'],
                                'lastName' => $checkout['customer']['last_name'],
                         )
                 );
              $data['checkout']['merchantHandlesConsumerData'] = true;
             } 
             $supportedTypes = [];
             $default = '';
             if(trim($settings['allowed_customer_type'])) {
                    $default = null;
                    switch($settings['allowed_customer_type']) {
                        case 'b2c' :
                            $supportedTypes = array('B2C');
                            $default = 'B2C';
                            break;
                        case 'b2b':
                            $supportedTypes = array('B2B');
                            $default = 'B2B';
                            break;
                        case 'b2c_b2b_b2c':
                            $supportedTypes = array('B2C', 'B2B');
                            $default = 'B2C';
                            break;
                        case 'b2b_b2c_b2b':
                            $supportedTypes = array('B2C', 'B2B');
                            $default = 'B2B';
                            break;
                }
                    $consumerType = array('supportedTypes' => $supportedTypes, 
                                          'default'=>$default);
                    if($consumerType) {
                         $checkout = $data['checkout'];
                         $checkout['consumerType'] = $consumerType;
                         $data['checkout'] = $checkout;
                     }
              }
             $x_url_complete = $this->request->get('x_url_complete');
             $data['checkout']['returnUrl'] = url('return') . "?x_url_complete={$x_url_complete}";
             $data['checkout']['integrationType'] = 'HostedPaymentPage';
             $appUrl = env('SHOPIFY_APP_URL');
             $callbackUrl = $this->request->get('x_url_callback');
             $x_gateway_reference = $this->request->get('x_reference');
             $x_account_id = $this->request->get('x_account_id');
             $x_amount = $this->request->get('x_amount');
             $x_currency = $this->request->get('x_currency');
             $shop = $settings['shop_url'];
             
             
             $x_reference = $this->request->get('x_reference');
             
             $url = "https://{$appUrl}/callback?callback_url={$callbackUrl}&x_reference=$x_reference";
                    
             $data['notifications'] = 
                 ['webhooks' => 
                    [['eventName' => 'payment.reservation.created', 
                     'url' => $url,
                     'authorization' => substr(str_shuffle(MD5(microtime())), 0, 10)]]
                 ];
    return $data;
    }

   protected function getRequestObjectItems($checkout = []) {
            $items = [];
            
            // Products
            foreach ($checkout['line_items'] as $item) {
               $unitPrice =  round($item['price'] / (1 + $this->getTaxRate($item)) * 100); //round($item['price'] * $item['quantity'] * 100);
               $taxRate =  round($this->getTaxRate($item) * 10000);
               $taxAmount = round($this->getTaxPrice($item) * 100);
               $grossTotalAmount = round($item['price'] * 100) * $item['quantity'];
               $netTotalAmount =  round($item['price'] *  $item['quantity'] / (1 + $this->getTaxRate($item)) * 100);
               $items[] = array(
                    'reference' => $item['product_id'],
                    'name' => str_replace(array('\'', '&'), '', $item['title']),
                    'quantity' => $item['quantity'],
                    'unit' => 'pcs',
                    'unitPrice' => $unitPrice,
                    'taxRate' => $taxRate,
                    'taxAmount' => $taxAmount,
                    'grossTotalAmount' => $grossTotalAmount,
                    'netTotalAmount' => $netTotalAmount);
            }
            //Shipping
            if($shippingLine = $this->getShippingLine($checkout)) { 
                $items[] = $shippingLine; 
            }

            //Discount
            if($this->getDiscountAmount($checkout) > 0) {
                $items[] = $this->discountRow($this->getDiscountAmount($checkout));
            }

            return $items;
    }

    protected function getShippingLine($checkout) {
        $shipping = [];
        if(!empty(($checkout['shipping_lines']))) {
            $current = current($checkout['shipping_lines']);
            $unitPrice = round($current['price'] / (1 + $this->getTaxRate($current)) * 100);  //round($current['price'] * 100);
            $taxRate =  round($this->getTaxRate($current) * 10000);
            $taxAmount = round($this->getTaxPrice($current) * 100);
            $grossTotalAmount = round($current['price'] * 100);
            $netTotalAmount =  round($current['price'] / (1 + $this->getTaxRate($current)) * 100);
            $shippingLine =  [
                    'reference' => $current['id'],
                    'name' => str_replace(array('\'', '&'), '', $current['title']),
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

    protected function getDiscountAmount($checkout) {
        $amount = 0;
        if(!empty($checkout['total_discounts'])) {
            $amount = $checkout['total_discounts'];
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

}
