<?php

namespace App\Http\Controllers\Accept;

use App\Http\Controllers\TestEnv;
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
