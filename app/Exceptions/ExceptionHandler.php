<?php


namespace App\Exceptions;


use Exception;
use HttpRequest;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

class ExceptionHandler extends \App\Exceptions\Handler
{
    public function report(Exception $exception)
    {
        parent::report($exception);
        $logger = $this->container->make(LoggerInterface::class);
        $request = request();
        $logger->error($exception->getMessage(), ['request' => $request->getContent()]);
    }
}
