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
       $this->logger->error('\App\Exceptions\ShopifyApiException ' . $e->getMessage());
       $this->logger->debug($add);
    }
}
