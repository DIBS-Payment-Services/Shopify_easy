<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Pay extends PayBase implements LiveEnv
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
           $this->easyApiExceptionHandler->handle($e, $request->all());
        }catch(\App\Exceptions\ShopifyApiException $e ) {
            $this->shopifyApiExceptionHandler->handle($e);
        }
        catch(\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $this->showErrorPage('Error ocurred...');
    }
}
