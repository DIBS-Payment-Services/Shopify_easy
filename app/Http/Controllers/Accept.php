<?php

namespace App\Http\Controllers;

class Accept extends AcceptBase implements LiveEnv
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
