<?php

namespace App\Http\Controllers;

class CaptureTest extends CaptureBase implements TestEnv
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $this->handle();
    }
}
