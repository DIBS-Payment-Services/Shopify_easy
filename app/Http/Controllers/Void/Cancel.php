<?php

namespace App\Http\Controllers\Void;

use Illuminate\Http\Request;

class Cancel extends CancelBase implements \App\Http\Controllers\LiveEnv
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
