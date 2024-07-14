<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\JsonResponse;

class Authenticate extends Middleware
{
    function handle($request, Closure $next, ...$guards)
    {
        $isThirdParty = in_array('third_party', $guards);

        if ($isThirdParty ) {
            if (config('app.api_token_disable')) {
                return $next($request);
            }

            if (!auth('third_party')->check()) {
                $checkBrowserName = $this->getBrowserName();
                if (!empty($checkBrowserName)){
                    return view('errors.404');
                }
                return response()->json(['message' => 'token expire'], JsonResponse::HTTP_UNAUTHORIZED);
            }

            if (auth('third_party')->user()->token != auth('third_party')->payload()->get('token')) {
                return response()->json(['message' => 'token expire'], JsonResponse::HTTP_UNAUTHORIZED);
            }

            return $next($request);
        }

        return parent::handle($request, $next, $guards);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }
    function getBrowserName(): string
    {
        $t = strtolower($_SERVER['HTTP_USER_AGENT']);
        $t = " " . $t;
        if (strpos($t, 'opera') || strpos($t, 'opr/')) {
            return 'Opera';
        } elseif (strpos($t, 'edge')) {
            return 'Edge';
        } elseif (strpos($t, 'chrome')) {
            return 'Chrome';
        } elseif (strpos($t, 'safari')) {
            return 'Safari';
        } elseif (strpos($t, 'firefox')) {
            return 'Firefox';
        } elseif (strpos($t, 'msie') || strpos($t, 'trident/7')) {
            return 'Internet Explorer';
        }

        return '';
    }
}
