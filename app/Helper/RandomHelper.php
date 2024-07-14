<?php

namespace App\Helper;

class RandomHelper
{
    public static function code_random($length = 6){
        $pool = '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }
    public static function int_code_random($length = 9){
        $pool = '0123456789';
        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }
}
