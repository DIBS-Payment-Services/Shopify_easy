<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use App\PaymentDetails;


class Test extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
       PaymentDetails::setCaptureRequestParams('8523933646945', 'fdvdfvd fv dfv dfvdfvdfvdfvd');
    }
}
