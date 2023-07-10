<?php

namespace App\Http\Middleware;

use Closure;
use App\Libs\Json\JsonResponse;
use App\Services\ExternalAPIs\CrmAPI;

class AuthTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Pre-Middleware Action
        $token = $request->bearerToken();

        if (!$token) {
            return JsonResponse::unauthorized('Token not provided');
        };

        // return ['test' => 'test'];

        // $crmAPI = new CrmAPI();
        // $data = $crmAPI->get('crm/category');

        // return $data;

        // $user = [
        //     'id' => '1',
        //     'name' => 'y',
        //     'email' => 'joy@mail.com'
        // ]

        $response = $next($request);

        // Post-Middleware Action

        return $response;
    }
}
