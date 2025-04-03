<?php

namespace App\Http\APIControllers;

use App\Contracts\APIClient;
use App\Http\Controllers\Controller;
use App\Models\PersonalAccessToken;
use Auth;
use Illuminate\Http\Request;
use Log;

class APIController extends Controller
{
    /**
     * @return [
     *      'user' => APIClient,
     *      'permissions' => array<string> | null,
     * ]
     */
    public function getClient(Request $request, bool $failIfUnauthorized = true): array
    {
        $m = Auth::guard('sanctum')->user();

        $token = null;

        if ($m) {
            $token = PersonalAccessToken::findToken($request->bearerToken());
        }

        Log::info($token);
        Log::info($m);

        return [
            'user' => $m,
            'permissions' => $token ? $token->abilities : null,
        ];
    }
}
