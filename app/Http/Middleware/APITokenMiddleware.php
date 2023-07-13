<?php

namespace App\Http\Middleware;

use Closure;
use App\Libs\Json\JsonResponse;
use Illuminate\Support\Str;
use App\Services\ExternalAPIs\CrmAPI;

class APITokenMiddleware
{
    public function handle($request, Closure $next)
    {
        $token = $request->header('Authorization');

        // Check if token not found or not match with format
        if (!$token || !Str::startsWith($token, 'Bearer ')) {
            return JsonResponse::unauthorized();
        }

        $bearerToken = Str::substr($token, 7);

        $crmAPI = new CrmAPI();
        $user = $crmAPI->get("user/token/check", [], $bearerToken);

        $request->merge(['user' => $user->data]);
        $request->merge(['token' => $bearerToken]);
        
        return $next($request);
    }
}
