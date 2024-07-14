<?php

namespace App\Repositories\UserBuy;


use App\Helper\Request;
use App\Models\Traffic;
use GuzzleHttp\Client;

class UserBuyRepository implements UserBuyRepositoryInterface
{
    private $apiUrl = '';

    private $apiToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.PmXsGJOSIMsYnfkwaihM54_ISBJy16lgaO7wpza-hGo';
    /***
     * @var Client
     */
    private $http;

    public function __construct()
    {
        $this->apiUrl = config('hocmai.hocmai_url').'/api/ldp/checkBoughtUser/';
        $this->http = new Client();
    }

    /***
     *
     * @param Traffic $traffic
     * @param array   $data
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPackageComboPackageInTime($userId, $startTime, $endTime)
    {

        $data = [
            'user_id' => $userId,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'token' => $this->apiToken,
        ];

        $option = ['form_params' => $data, 'verify' => false];

        $data = Request::post($this->apiUrl, $option);

        return json_decode($data->getBody());
    }

}
