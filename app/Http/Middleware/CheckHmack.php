<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckHmack
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!$this->checkHmac($request)) {
          die("unathorizesd access");
        }
        return $next($request);
    }

    /**
     *
     * @param Request $request
     * @return boolean
     */
    private function checkHmac(Request $request)
    {
        $requestParams = $request->all();
        ksort($requestParams);
        unset($requestParams['hmac']);
        $message = http_build_query($requestParams);
        return ($request->get('hmac') == hash_hmac('sha256', $message, env('SHOPIFY_API_SECRET')));
    }
}
