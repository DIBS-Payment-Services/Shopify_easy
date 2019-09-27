<?php

namespace App\Exceptions;

/**
 * Description of ShopifyApiExceptionHandler
 *
 * @author mabe
 */
class ShopifyApiExceptionHandler {
    
    private $logger;
    
    public function __construct(\Illuminate\Log\Logger $logger) {
        $this->logger = $logger;
    }

    public function handle(\App\Exceptions\ShopifyApiException $e, array $add = null) {
       $stackTrace = $e->getTraceAsString();
       $this->logger->error('\App\Exceptions\ShopifyApiException ' . 
                             PHP_EOL . $e->getMessage() . PHP_EOL . $stackTrace);
       if($add) {
           $this->logger->debug($add);
       }
       return $e->getMessage();
    }
}
