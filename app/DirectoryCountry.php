<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DirectoryCountry extends Model
{
    protected $table = 'directory_country';
    
    /**
     * 
     * @param type $iso2code
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getCountry($iso2code)
    {
       return self::query()->where('iso2_code', $iso2code)->get(['iso3_code']);
    }

}
