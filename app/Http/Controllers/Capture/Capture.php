<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\Capture;

/**
 * Description of Lara
 *
 * @author mabe
 */
class Capture extends CaptureBase implements \App\Http\Controllers\LiveEnv {
    
    public function __invoke() {
        
        return $this->handle();
    }
}
