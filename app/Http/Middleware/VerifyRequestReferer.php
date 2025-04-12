<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class VerifyRequestReferer
{
    public function handle(Request $request, Closure $next): Response
    {
        $referer = $request->headers->get('referer');

        if (!Str::contains($referer, Config::get('app.url'))) {
            return response()->json([
                'message' => 'forbidden',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
