<?php

namespace App\Exceptions;

/**
 * Description of EasyExceptionHandler
 *
 * @author mabe
 */
class EasyApiExceptionHandler {
    
    private $logger;
    
    public function __construct(\Illuminate\Log\Logger $logger) {
        $this->logger = $logger;
    }
    
    public function handle(\App\Exceptions\EasyException $e, array $add = null) {
        $prefixMessage = 'Exception call to Easy Api. ';
        $message = '';
        $errorLocation = ' File: ' . $e->getFile(). ' Line: ' . $e->getLine();
        switch($e->getCode()) {
                case 400:
                   $message = 'Bad request: ' . $e->getMessage();
                break;
                case 401:
                    $message = 'Unauthorized access. Try to check Easy secret/live key';
                break;
                case 404:
                    $message = 'Payment or charge not found';
                break;
                case 500:
                    $message = 'Unexpected error';
                break;
        }
        $this->logger->error($prefixMessage . $message . $errorLocation);
        if($add) {
            $this->logger->debug($add);
        }
       return $message;
    }
}
