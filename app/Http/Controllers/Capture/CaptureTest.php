<?php

namespace App\Http\Controllers\Capture;

class CaptureTest extends CaptureBase implements \App\Http\Controllers\TestEnv
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
