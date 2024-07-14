<?php

namespace App\Lib;

use App\Helper\Request;

class HocMaiShortLink
{
    private $apiLink = 'https://hocmai.link/api/v2/links';

    private $token = 'DG5msEzGGaNjYE~a1yKMx8AySenXV6VhtFVtEA0L';

    public function __construct()
    {

    }

    /***
     * @param $phone
     * @param $message
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handler($link, $code)
    {
        $data = Request::post($this->apiLink, [
            'headers' => [
                'X-API-KEY' => $this->token
            ],
            'form_params' => [
                'target' => $link,
                'customurl' => $code,
                'domain' => 'hocmai.link',
                'expire_in' => '15 days',
            ]
        ]);

        try {
            $resData = @json_decode((string)$data->getBody());

            if ($resData) {
                return $resData->link;
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            return false;
        }
    }
}
