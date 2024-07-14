<?php

namespace App\Repositories\Traffic;


use App\Helper\Request;
use App\Models\RequestLog;
use App\Models\Traffic;
use App\Repositories\Traffic\TrafficRepositoryInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Http;

class TrafficRepository implements TrafficRepositoryInterface
{
    private $apiUrl = '';
    /***
     * @var Client
     */
    private $http;

    public function __construct()
    {
        $this->apiUrl = config('hocmai.hocmai_url').'api/crm/traffic';
        $this->http = new Client();
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
    public function pushToHocMai(Traffic $traffic, array $data)
    {

        if (!config('hocmai.enable_push_traffic')) {
            return false;
        }

        $traffic->landingPage;

        $landingPage = $traffic->landingPage;

        if ($landingPage) {
            $data['uri'] = $data['uri'].'?'.$data['query_string'];
            $data['landing_page'] = $landingPage->code;
            $data['session_id'] = $traffic->session_id;
            $data['utm_campaign'] = $traffic->utm_campaign ?? $data['utm_campaign'];
            $data['utm_source'] = $traffic->utm_source ?? $data['utm_source'];
            $data['utm_medium'] = $traffic->utm_medium ?? $data['utm_medium'];
            $data['user_id'] = $traffic->user_id;
            $data['client_ip'] = $traffic->register_ip;
        }
        $option = ['query' => $data, 'verify' => false];

        try {
            Request::request($this->apiUrl, 'GET', $option);

            return true;
        } catch (\Exception $exception) {
            return false;
        }

    }

}
