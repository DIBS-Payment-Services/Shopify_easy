<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class RefundTest extends RefundBase implements TestEnv
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $this->handle();
    }
}
