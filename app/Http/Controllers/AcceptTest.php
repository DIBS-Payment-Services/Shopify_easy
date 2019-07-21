<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AcceptTest extends AcceptBase implements TestEnv
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
