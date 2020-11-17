<?php

namespace App\Http\Controllers\Accept;

class Accept extends AcceptBase implements \App\Http\Controllers\LiveEnv
{

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        return $this->handle();
    }

}
