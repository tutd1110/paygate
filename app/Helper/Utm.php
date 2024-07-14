<?php

namespace App\Helper;


use App\Models\Utm\UtmCampaign;
use App\Models\Utm\UtmContent;
use App\Models\Utm\UtmCreator;
use App\Models\Utm\UtmMedium;
use App\Models\Utm\UtmSource;
use App\Models\Utm\UtmTerm;

class Utm
{
    static function campaign($text)
    {
        $utm = null;

        if ($text) {
            $utm = UtmCampaign::firstOrCreate(['text' => $text]);
        }

        return $utm;

    }

    static function content($text)
    {
        $utm = null;

        if ($text) {
            $utm = UtmContent::firstOrCreate(['text' => $text]);
        }

        return $utm;

    }

    static function creator($text)
    {
        $utm = null;

        if ($text) {
            $utm = UtmCreator::firstOrCreate(['text' => $text]);
        }

        return $utm;

    }

    static function medium($text)
    {
        $utm = null;

        if ($text) {
            $utm = UtmMedium::firstOrCreate(['text' => $text]);
        }

        return $utm;
    }

    static function source($text)
    {
        $utm = null;

        if ($text) {
            $utm = UtmSource::firstOrCreate(['text' => $text]);
        }

        return $utm;
    }

    static function term($text)
    {
        $utm = null;

        if ($text) {
            $utm = UtmTerm::firstOrCreate(['text' => $text]);
        }

        return $utm;
    }


}
