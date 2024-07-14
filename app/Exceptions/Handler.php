<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport
        = [
            //
        ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash
        = [
            'current_password',
            'password',
            'password_confirmation',
        ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {


        });

        $this->renderable(function (\Exception $e) {
            if (!empty(env('APP_PRODUCTION_EX'))) {
                //mảng path_info được loại trừ để hiển thị responseCode trả về khi bị lỗi
                $arr_path_info_except = [
                    '/api/v1/pgw/vnpay/paybill',
                    '/api/v1/wheels/run-wheel-fahasa',
                ];
                if ($_SERVER['REQUEST_METHOD'] == 'GET' && !in_array($this->get_path_info($_SERVER['REQUEST_URI']), $arr_path_info_except)) {
                    return redirect()->route('server_error');
                }
            }

            if ($e instanceof ValidationException) {

            } elseif ($e instanceof PaymentException) {
                return response()->json($e->getResponse());
            } elseif ($e instanceof InvalidArgumentException) {
                return response()->json([
                    'code' => 406,
                    'message' => $e->getMessage(),
                    'data' => []
                ], 406);
            } elseif ($e instanceof ResourceNotFoundException) {
                return response()->json([
                    'code' => 404,
                    'message' => $e->getMessage(),
                    'data' => []
                ], 404);
            } else {
                return response()->json([
                    'message' => $e->getMessage(),
                    'data' => [
                        'url' => URL::current(),
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTrace(),
                    ]
                ], 500);
            }
        });
    }

    private function get_path_info($request_uri)
    {
        $parts = parse_url($request_uri);
        $path_info = $parts['path'];
        if (!empty($parts['query'])) {
            $execpt_path_info = '?' . $parts['query'];
            $path_info = str_replace($execpt_path_info, '', strval($request_uri));
        }
        return $path_info;
    }
}
