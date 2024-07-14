<?php

namespace App\Helper;

class ShortLink
{
    static function handler($link, $code)
    {
        return app('hocmai_short_link')->handler($link, $code);
    }
}
