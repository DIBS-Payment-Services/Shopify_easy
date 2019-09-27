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
        $prefixMessage = 'Exception call to Easy Api. ' . PHP_EOL;
        $stackTrace = 'Stack trace: ' . PHP_EOL . $e->getTraceAsString();
        $message = 'Response code:  ' . $e->getCode() . PHP_EOL . 'Message: ';
        switch($e->getCode()) {
                case 400:
                   $message .= 'Bad request: ' . $e->getMessage();
                break;
                case 401:
                    $message .= 'Unauthorized access. Try to check Easy secret/live key';
                break;
                case 402:
                    $message .= 'Payment required';
                break;
                case 404:
                    $message .= 'Payment or charge not found';
                break;
                case 500:
                    $message .= 'Unexpected error';
                break;
                case 0:
                    $message .= 'Curl error: ' . $e->getMessage();
                break;
        }

        $this->logger->error($prefixMessage . $message . PHP_EOL . $stackTrace);
        if($add) {
            $this->logger->debug($add);
        }
       return $message;
    }

    /**
     * Parse json error message and fetch error message readable for users
     * 
     * @param string $msgJson
     */
    public function parseError( $msgJson ) {
       $msgArr = json_decode($msgJson, true);
       $errorStr = '';
       if(isset($msgArr['errors'])) {
          foreach($msgArr['errors'] as $k => $v) {
              foreach( $v as $error ) {
                   $errorStr .= $error ;
              }
          }
       }
       return $errorStr;
    }
}
