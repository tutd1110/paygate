<?php

namespace App\Helper;

use App\Models\RequestLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Redirect;


class Mycurl
{
    const TIME_OUT = 120;

    public static function postCurl($url, $option = [])
    {
        try {

            $optionClient = array(
                'timeout' => self::TIME_OUT,
                'verify' => false
            );
            $requestLog = new RequestLog;
            $requestLog->fill([
                'url' => $url,
                'option' => json_encode($option),
                'method' => 'POST',
            ]);
            $client = new Client($optionClient);
            $response = $client->request('POST',$url, $option);
            $content = $response->getBody()->getContents();

            $result = json_decode($content, true);
            $requestLog->fill([
                'response' => (string)$response->getBody(),
                'is_success' => 1,
                'status_code' => $response->getStatusCode(),
                'headers' => json_encode($response->getHeaders()),
            ]);
            $requestLog->save();

            return $result;
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

    public static function putCurl($url, $option = [])
    {
        try {
            $optionClient = array(
                'timeout' => self::TIME_OUT,
                'verify' => false
            );
            $client = new Client($optionClient);

            $response = $client->put($url, $option);
            $content = $response->getBody()->getContents();

            $result = json_decode($content, true);

            return $result;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public static function deleteCurl($url, $option = [])
    {
        try {

            $optionClient = array(
                'timeout' => self::TIME_OUT,
                'verify' => false
            );

            $client = new Client($optionClient);
            $response = $client->delete($url,$option);
            $content = $response->getBody()->getContents();

            $result = json_decode($content, true);

            return $result;
        } catch (\Exception $e) {
            $line = $e->getLine();
            $code = !empty($e->getCode()) ? $e->getCode() : 400;
            return BadRequest::notificationBadRequest($e->getMessage(), $code, $line);
        }
    }

    public static function getCurl($url, $option = [])
    {
        try {
            $optionClient = array(
                'timeout' => self::TIME_OUT,
                'verify' => false
            );

            $client = new Client($optionClient);
            $response = $client->get($url,$option);
            $content = $response->getBody()->getContents();

            $result = json_decode($content, true);

            return $result;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

}
