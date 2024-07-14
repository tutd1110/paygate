<?php

namespace App\Lib;

use Illuminate\Support\Str;

class FormatPhoneNumber
{
    static function toBasic($phone)
    {
        /***
         * remove special character
         */
        $phone = Str::slug($phone, '');

        /***
         * remove 84 if exist in first phone
         */
        if (substr($phone, 0, 2) == 84) {
            $phone = '0'.substr($phone, 2, strlen($phone));

        }

        return $phone;
    }
}
