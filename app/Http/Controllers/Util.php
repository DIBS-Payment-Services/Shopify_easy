<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Util extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
       $result = ['decrypted_key' => 'Cherchill'];
       if($request->isMethod('POST')) {
          $decrypted_key = openssl_decrypt($request->get('key'), 'AES-128-ECB', env('EASY_KEY_SALT'));
          $result = ['decrypted_key' => $decrypted_key];
       }

       return view('util', $result);
    }
}
