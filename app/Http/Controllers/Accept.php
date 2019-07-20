<?php

namespace App\Http\Controllers;

class Accept extends AcceptBase
{
    
    const ENV = 'live';
    const KEY = 'easy_secret_key';
    
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
