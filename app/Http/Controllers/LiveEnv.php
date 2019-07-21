<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers;

/**
 *
 * @author mabe
 */
interface LiveEnv {
    const ENV = 'live';
    const KEY = 'easy_secret_key';
}
