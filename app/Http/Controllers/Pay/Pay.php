<?php

namespace App\Http\Controllers\Pay;

use Illuminate\Http\Request;

class Pay extends PayBase implements \App\Http\Controllers\LiveEnv
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
           if($e->getCode() == 400) {
              $parsedErrorMessage = $this->easyApiExceptionHandler->parseError($e->getMessage());
           }
        }catch(\App\Exceptions\ShopifyApiException $e) {
            $this->shopifyApiExceptionHandler->handle($e);
        }
        catch(\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        $errorMsg = '';
        if(isset($parsedErrorMessage) ) {
            $errorMsg = $parsedErrorMessage;
        }
        return redirect($this->request->get('x_url_complete'));
    }
}
