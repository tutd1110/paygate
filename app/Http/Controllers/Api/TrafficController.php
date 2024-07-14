<?php

namespace App\Http\Controllers\Api;

use App\Helper\Utm;
use App\Http\Controllers\Controller;
use App\Http\Requests\Traffic\TrafficFilter;
use App\Http\Requests\Traffic\TrafficRequest;
use App\Models\Campaign;
use App\Models\Traffic;
use App\Repositories\Traffic\TrafficRepository;
use App\Repositories\Traffic\TrafficRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrafficController extends Controller
{
    /***
     * @var Traffic
     */
    private $mainModel;


    public function __construct(Traffic $traffic)
    {
        $this->mainModel = $traffic;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function index(TrafficFilter $request)
    {

        $query = $this->mainModel;

        $filter = $request->all();


        if (isset($filter['landing_page_id'])) {
            if (is_array($filter['landing_page_id'])) {
                $query = $query->whereIn('landing_page_id', $filter['landing_page_id']);
            } else {
                $query = $query->where('landing_page_id', $filter['landing_page_id']);
            }
        }

        $queryListUtmSource = clone $query;
        $queryListUtmCampaign = clone $query;

        if (isset($filter['start_date'])) {
            $query = $query->where('created_at', '>=',
                Carbon::createFromFormat('Y-m-d', $filter['start_date'])->startOfDay()->format('Y-m-d H:i:s'));
        }

        if (isset($filter['end_date'])) {
            $query = $query->where('created_at', '<=',
                Carbon::createFromFormat('Y-m-d', $filter['end_date'])->endOfDay()->format('Y-m-d H:i:s'));
        }


        if (isset($filter['utm_campaign'])) {
            if (is_array($filter['utm_campaign'])) {
                $query = $query->whereIn('utm_campaign', $filter['utm_campaign']);
            } else {
                $query = $query->where('utm_campaign', $filter['utm_campaign']);
            }
        }

        if (isset($filter['utm_source'])) {
            if (is_array($filter['utm_source'])) {
                $query = $query->whereIn('utm_source', $filter['utm_source']);
            } else {
                $query = $query->where('utm_source', $filter['utm_source']);
            }
        }

        if (isset($filter['utm_medium'])) {
            if (is_array($filter['utm_medium'])) {
                $query = $query->whereIn('utm_medium', $filter['utm_medium']);
            } else {
                $query = $query->where('utm_medium', $filter['utm_medium']);
            }
        }


        if (isset($filter['group_by'])) {
            if (is_array($filter['group_by'])) {
                foreach ($filter['group_by'] as $value) {
                    $query = $query->groupBy($value);
                }
            } else {
                $query = $query->groupBy($filter['group_by']);
            }
        }


        if (isset($filter['order'])) {
            if (is_array($filter['order'])) {
                foreach ($filter['order'] as $key => $value) {
                    $query = $query->orderBy($value, $filter['direction'][$key] ?? 'asc');
                }
            } else {
                $query = $query->orderBy($filter['order'], $filter['direction'] ?? 'asc');
            }
        }
        if (!($filter['group_by'] ?? false)) {
            if ($request->get('count')) {
                $listTraffics = $query->paginate($request->get('limit', 20));
            } else {
                $listTraffics = $query->simplePaginate($request->get('limit', 20));
            }
        } else {
            $query = $query->select('*', DB::raw('COUNT(id) as count_traffic'));
            if (isset($filter['with'])) {
                if (in_array('count_contact', $filter['with'])) {
                    $query = $query->withCount('contacts');
                }
            }
            $listTraffics = $query->get();
        }
        $data = [
            'traffics' => $listTraffics,
        ];

        if (in_array('utm_campaigns', $request->get('append_data', []))) {
            $listCampaign = $queryListUtmCampaign->select('utm_campaign')->groupBy('utm_campaign')->get();
            $data['utm_campaigns'] = $listCampaign;
        }

        if (in_array('utm_sources', $request->get('append_data', []))) {
            $listUtmSource = $queryListUtmSource->select('utm_source')->groupBy('utm_source')->get();
            $data['utm_sources'] = $listUtmSource;
        }

        return response()->json([
            'message' => 'get success',
            'data' => $data,
        ]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(TrafficRequest $request)
    {
        /***
         * @var $trafficRes TrafficRepository
         */
        $trafficRes = app()->make(TrafficRepositoryInterface::class);


        $data = $request->all([
            'landing_page_id',
            'cookie_id',
            'utm_medium',
            'utm_source',
            'utm_campaign',
            'register_ip',
            'user_id',
            'session_id',
            'uri',
            'query_string',
        ]);

        if (!$data['utm_medium']) {
            $data['utm_medium'] = 'direct';
        }

        if (!$data['utm_source']) {
            $data['utm_source'] = 'direct';
        }

        if (!$data['utm_campaign']) {
            $data['utm_campaign'] = 'none';
        }

        $utmCampaign = Utm::campaign( $data['utm_campaign'] ?? '');
        $utmContent = Utm::content( $data['utm_content'] ?? '');
        $utmCreator = Utm::creator( $data['utm_creator'] ?? '');
        $utmMedium = Utm::medium( $data['utm_medium'] ?? '');
        $utmSource = Utm::source( $data['utm_source'] ?? '');
        $utmTerm = Utm::term( $data['utm_term'] ?? '');

        $utmProcessArray = [
            'utm_campaign_id' => $utmCampaign->id ?? 0,
            'utm_content_id' => $utmContent->id ?? 0,
            'utm_creator_id' => $utmCreator->id ?? 0,
            'utm_medium_id' => $utmMedium->id ?? 0,
            'utm_source_id' => $utmSource->id ?? 0,
            'utm_term_id' => $utmTerm->id ?? 0,
        ];

        $data = array_merge($data, $utmProcessArray);

        $campaign = Campaign::where('code', $request->input('utm_campaign'))->first();

        $data['user_id'] ?: $data['user_id'] = 0;
        $data['uri'] = rtrim($data['uri'], '/');
        if ($campaign) {
            $data['campaign_id'] = $campaign->id;
        } else {
            $data['campaign_id'] = 0;
        }


        $traffic = $this->mainModel->where('uri', $data['uri'])->where('session_id', $data['session_id'])->first();

        if (!$traffic) {
            $traffic = $this->mainModel->create($data);
        }


        $sendToHocMai = $trafficRes->pushToHocMai($traffic, [
            "fsuid" => $request->input("fsuid"),
            "uri" => $request->input("uri"),
            "query_string" => $request->input("query_string"),
            "utm_campaign" => $request->input("utm_campaign"),
            "utm_source" => $request->input("utm_source"),
            "utm_medium" => $request->input("utm_medium"),
            "utm_term" => $request->input("utm_term"),
            "utm_content" => $request->input("utm_content"),
        ]);

        return response()->json([
            'message' => 'save success',
            'data' => [
                'traffic' => $traffic,
                'sendToHocMai' => $sendToHocMai,
            ]
        ]);

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function show($id)
    {
        $traffic = $this->mainModel->find($id);

        return response()->json([
            'message' => 'get success',
            'data' => [
                'traffic' => $traffic
            ]
        ]);
    }

}
