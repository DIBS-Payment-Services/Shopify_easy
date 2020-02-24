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
       $message = '\App\Exceptions\ShopifyApiException ' .
           PHP_EOL . $e->getMessage() . PHP_EOL . $stackTrace . PHP_EOL;
       $this->logger->error($message);
       syslog(LOG_CRIT, 'shopify.easy.exception: ' . $message);
       if($add) {
           $this->logger->debug($add);
           syslog(LOG_CRIT,  print_r($add, true));

       }
       return $e->getMessage();
    }
}
