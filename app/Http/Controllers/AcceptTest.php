<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AcceptTest extends AcceptBase
{
    
    const ENV = 'test';
    const KEY = 'easy_test_secret_key';
    
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        return $this->handle();
    }
}
