<?php

namespace App\Repositories\RtaProduct;


use App\Helper\Request;
use App\Models\Traffic;

class RtaProductRepository implements RtaProductRepositoryInterface
{
    private $apiUrl = '';
    /***
     * @var Client
     */
    private $http;

    private $token = '35178ad77757fb57cf110615ca6bb997';

    public function __construct()
    {
        $this->apiUrl = config('hocmai.hocmai_url').'api/rta/products';
    }

    /***
     *
     * @param Traffic $traffic
     * @param array   $data
     *
     * utm_campaign=122344
     * session_id=thuy
     * uri=/123/12343?dainq=1
     * fsuid=
     * client_ip=192.168.2.3
     * user_id=smt
     * landing_page=AT-GIAIPHAPPEN-2022
     * utm_source=smt
     * utm_medium=smt
     * utm_term=smt
     * utm_content=smt
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAll()
    {

        $option = [
            'form_params' => [
                'token' => $this->token,
            ],
            'verify' => false,
        ];


        try {
            $res = Request::request($this->apiUrl, 'POST', $option);

            $resData = json_decode($res->getBody());

            if ($resData->status == 'success') {
                return $resData->products;
            } else {
                return [];
            }
        } catch (\Exception $exception) {
            return [];
        }

    }

}
