<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadSqliteController extends Controller
{
    public function __invoke(Request $request): BinaryFileResponse
    {
        $token = $request->input('token');

        if ($token !== Config::get('auth.database_token')) {
            abort(403, 'Unauthorized.');
        }

        $path = database_path('database.sqlite');

        if (!file_exists($path)) {
            abort(404, 'File not found.');
        }

        return response()->download($path, 'database.sqlite', [
            'Content-Type' => 'application/octet-stream',
        ]);
    }
}
