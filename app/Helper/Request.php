<?php

namespace App\Helper;


use App\Models\RequestLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class Request
{
    /****
     * @param       $url
     * @param       $method
     * @param array $option
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    static function request($url, $method, array $option = [])
    {

        $http = new Client();
        $requestLog = new RequestLog();
        $requestLog->fill([
            'url' => $url,
            'option' => json_encode($option),
            'method' => $method,
        ]);

        try {
            $option['verify'] = false;
            $res = $http->request($method, $url, $option);
            $requestLog->fill([
                'response' => (string)$res->getBody(),
                'is_success' => 1,
                'status_code' => $res->getStatusCode(),
                'headers' => json_encode($res->getHeaders()),
            ]);
            $requestLog->save();

            return $res;
        } catch (ClientException $exception) {
            $requestLog->fill([
                'status_code' => $exception->getResponse()->getStatusCode(),
                'headers' => json_encode($exception->getResponse()->getHeaders()),
                'response' => (string)$exception->getResponse()->getBody(),
                'is_success' => 0,
                'exception_info' => json_encode([
                    'line' => $exception->getLine(),
                    'file' => $exception->getFile(),
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTrace(),
                ]),
            ]);

            throw $exception;
        } catch (\Exception $exception) {
            $requestLog->fill([
                'is_success' => 0,
                'exception_info' => json_encode([
                    'line' => $exception->getLine(),
                    'file' => $exception->getFile(),
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTrace(),
                ]),
            ]);

            throw $exception;
        } finally {
            $requestLog->save();
            if (in_array($url, [
                    'https://hocmai.vn/api/crm/traffic',
                ])
                && $requestLog->is_success) {
                /***
                 * Nếu request là traffic thì tắt đi do đang nhiều quá chỉ log traffic bị lỗi
                 */
                $requestLog->delete();
            }
        }
    }

    /***
     * @param       $url
     * @param array $option
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    static function get($url, array $option = [])
    {
        return self::request($url, 'GET', $option);
    }


    /***
     * @param       $url
     * @param array $option
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    static function post($url, array $option = [])
    {
        return self::request($url, 'POST', $option);
    }

    /****
     * @param       $url
     * @param array $option
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    static function put($url, array $option = [])
    {
        return self::request($url, 'PUT', $option);
    }


    /***
     * @param       $url
     * @param array $option
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    static function delete($url, array $option = [])
    {
        return self::request($url, 'DELETE', $option);
    }
}
