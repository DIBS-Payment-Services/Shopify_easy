<?php

namespace App\Http\Controllers\Void;

use Illuminate\Http\Request;

class CancelTest extends CancelBase implements \App\Http\Controllers\TestEnv
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
