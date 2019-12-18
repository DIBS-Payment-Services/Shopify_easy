<?php

namespace App\Http\Controllers\Pay;

use Illuminate\Http\Request;

class PayTest extends PayBase implements \App\Http\Controllers\TestEnv
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        try {
           return $this->startPayment($request);
        }catch(\App\Exceptions\EasyException $e) {
           $message  = $this->easyApiExceptionHandler->handle($e, $request->all());
        }catch(\App\Exceptions\ShopifyApiException $e ) {
           $message = $this->shopifyApiExceptionHandler->handle($e);
        }
        catch(\Exception $e) {
           $message = $this->logger->error($e->getMessage());
        }
        return $this->showErrorPage($message);
    }
}
