<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;

class LogClientRequest
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }


    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except
        = [
            '/api/v1/departments*',
            '/api/v1/traffics*',
            '/api/v1/pgw/checkbill',
        ];

    protected $forceLog
        = [
            '/api/v1/wheels/run-wheel-fahasa',
            '/api/v1/pgw/vnpay/paybill',
            '/api/v1/zns*',
            '/api/v1/public/contacts',
//            '/api/v2/payments*',
//            '/api/v2/public/payments*',
        ];

    /**
     * Handle tasks after the response has been sent to the browser.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \Illuminate\Http\Response $response
     *
     * @return void
     */
    public function terminate(Request $request, $response)
    {
        if ((!$this->inExceptArray($request) && $request->method() != 'GET') || ($this->inForceLog($request))) {
            try {
                \App\Models\LogClientRequest::create([
                    'url' => URL::current(),
                    'ip' => $request->ip(),
                    'status_code' => $response->getStatusCode(),
                    'data' => json_encode($request->all()),
                    'method' => $request->method(),
                    'header' => json_encode($request->header()),
                    'response' => (string)($response->getContent())
                ]);
            } catch (\Exception $exception) {
            }
        }


    }

    public function inForceLog($request)
    {
        foreach ($this->forceLog as $urlLog) {
            if ($urlLog !== '/') {
                $urlLog = trim($urlLog, '/');
            }

            if ($request->fullUrlIs($urlLog) || $request->is($urlLog)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $request
     *
     * @return bool
     */
    private function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
