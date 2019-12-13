<?php

namespace App\Http\Controllers\Accept;

use Illuminate\Http\Request;

class AcceptTest extends AcceptBase implements \App\Http\Controllers\TestEnv
{
    
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
