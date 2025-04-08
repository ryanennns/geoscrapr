<?php

namespace App;

use Illuminate\Support\Facades\Config;

class GeoGuessrHttp
{
    const BASE_URL = 'https://www.geoguessr.com/';
    const HEADERS = [
        "content-type"       => "application/json",
        "sec-ch-ua"          => "\"Google Chrome\";v=\"129\", \"Not=A?Brand\";v=\"8\", \"Chromium\";v=\"129\"",
        "sec-ch-ua-mobile"   => "?0",
        "sec-ch-ua-platform" => "\"Linux\"",
        "x-client"           => "web",
    ];

    public static function cookieString(): string
    {
        $deviceToken = Config::get('geo.device_token');
        $cfuvid = Config::get('geo.cfuvid');
        $ncfa = Config::get('geo.ncfa');
        $session = Config::get('geo.session');

        return "devicetoken=$deviceToken; _cfuvid=$cfuvid; _ncfa=$ncfa; _session=$session";
    }
}
