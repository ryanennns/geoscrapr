<?php

namespace App;

use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class GeoGuessrHttp
{
    public const string BASE_URL = 'https://www.geoguessr.com/';

    public const string RATINGS_ENDPOINT = 'api/v4/ranked-system/ratings';

    public const string TEAM_RATINGS_ENDPOINT = 'api/v4/ranked-team-duels/ratings';

    public const array HEADERS = [
        'content-type'       => 'application/json',
        'sec-ch-ua'          => '"Google Chrome";v="129", "Not=A?Brand";v="8", "Chromium";v="129"',
        'sec-ch-ua-mobile'   => '?0',
        'sec-ch-ua-platform' => '"Linux"',
        'x-client'           => 'web',
    ];

    public static function cookieString(): string
    {
        $deviceToken = Config::get('geo.device_token');
        $cfuvid = Config::get('geo.cfuvid');
        $ncfa = Config::get('geo.ncfa');
        $session = Config::get('geo.session');

        return "devicetoken=$deviceToken; _cfuvid=$cfuvid; _ncfa=$ncfa; _session=$session";
    }

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    public static function rankedSystemRatings(int $offset, int $limit, ?string $gameMode): PromiseInterface|Response
    {
        $response = Http::withHeaders([
            ...self::HEADERS,
            'cookie' => self::cookieString(),
        ])->get(self::BASE_URL . self::RATINGS_ENDPOINT, [
            'offset'   => $offset,
            'limit'    => $limit,
            'gameMode' => $gameMode,
        ]);

        if (! $response->successful()) {
            throw new Exception("Request to GeoGuessr Rating API failed with status {${$response->status()}}");
        }

        return $response;
    }

    /**
     * @throws ConnectionException
     */
    public static function rankedTeamRatings(int $offset, int $limit): PromiseInterface|Response
    {
        $response = Http::withHeaders([
            ...GeoGuessrHttp::HEADERS,
            'cookie' => GeoGuessrHttp::cookieString(),
        ])->get(GeoGuessrHttp::BASE_URL . self::TEAM_RATINGS_ENDPOINT, [
            'offset' => $offset,
            'limit'  => $limit,
        ]);

        if (! $response->successful()) {
            throw new Exception("Request to GeoGuessr Teams API failed with status {${$response->status()}}");
        }

        return $response;
    }
}
