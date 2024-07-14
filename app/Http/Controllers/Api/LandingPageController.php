<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LandingPage\LandingPageFilterRequest;
use App\Http\Requests\LandingPage\LandingPageRequest;
use App\Models\LandingPage;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LandingPageController extends Controller
{
    /***
     * @var LandingPage
     */
    public $_mainModel;

    public function __construct(LandingPage $landingPage)
    {
        $this->_mainModel = $landingPage;
    }

    /****
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(LandingPageFilterRequest $request)
    {

        $filter = $request->all();

        $query = $this->_mainModel;

        if (isset($filter['id'])) {
            if (is_array($filter['id'])) {
                $query = $query->whereIn('id', $filter['id']);
            } else {
                $query = $query->where('id', $filter['id']);
            }
        }

        if (isset($filter['code'])) {
            if (is_array($filter['code'])) {
                $query = $query->whereIn('code', $filter['code']);
            } else {
                $query = $query->where('code', $filter['code']);
            }
        }

        if (isset($filter['department_id'])) {
            if (is_array($filter['department_id'])) {
                $query = $query->whereIn('department_id', $filter['department_id']);
            } else {
                $query = $query->where('department_id', $filter['department_id']);
            }
        }
        if (isset($filter['site_id'])) {
            if (is_array($filter['site_id'])) {
                $query = $query->whereIn('site_id', $filter['site_id']);
            } else {
                $query = $query->where('site_id', $filter['site_id']);
            }
        }
        if (isset($filter['domain_name'])) {
            if (is_array($filter['domain_name'])) {
                $query = $query->whereIn('domain_name', $filter['domain_name']);
            } else {
                $query = $query->where('domain_name', $filter['domain_name']);
            }
        }
        if (isset($filter['server_ip'])) {
            if (is_array($filter['server_ip'])) {
                $query = $query->whereIn('server_ip', $filter['server_ip']);
            } else {
                $query = $query->where('server_ip', $filter['server_ip']);
            }
        }
        if (isset($filter['status'])) {
            $query = $query->where('status', $filter['status']);
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
        if (isset($filter['get_all'])) {
            $query = $query->get();
            $landingPages = [];
            foreach ($query as $key => $value){
                $landingPages[$value['id']] = $value;

        }
        }else {
            $landingPages = $query->paginate($request->get('limit', 20));
        }

        return response()->json([
            'message' => 'get success',
            'data' => [
                'landingPages' => $landingPages
            ]
        ]);

    }


    /***
     * @param LandingPageRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(LandingPageRequest $request)
    {
        /****
         * @var $landingPage LandingPage
         */
        $landingPage = $this->_mainModel->create($request->all());

        $landingPage->campaigns()->sync($request->input('campaign_ids'));
        if ($request->input('partner_ids', [])) {
            $landingPage->apiPartners()->sync($request->input('partner_ids', []));
            $landingPage->apiPartners;
        }


        return response()->json([
            'message' => 'save success',
            'data' => [
                'landingPage' => $landingPage
            ]
        ]);

    }

    /***
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        /***
         * @var $landingPage LandingPage
         */
        $landingPage = $this->_mainModel->find($id);

        $landingPage->makeVisible(['server_ip']);

        if (!$landingPage) {
            throw new NotFoundHttpException('landing page not exist');
        }

        $landingPage->apiPartners;


        return response()->json([
            'message' => 'get success',
            'data' => [
                'landingPage' => $landingPage
            ]
        ]);
    }

    /***
     * @param LandingPageRequest $request
     * @param                    $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(LandingPageRequest $request, $id)
    {
        $landingPage = $this->_mainModel->find($id);

        $landingPage->fill($request->validated());
        $save = $landingPage->save();

        $landingPage->campaigns()->sync($request->input('campaign_ids'));
        if ($request->input('partner_ids', [])) {
            $landingPage->apiPartners()->sync($request->input('partner_ids', []));
        }else{
            $partner_ids = $request->input('partner_ids');
            if (!isset($partner_ids)){
                $partner_ids = [];
                $landingPage->apiPartners()->sync($partner_ids);
            }
        }


        return response()->json([
            'message' => 'update success',
            'data' => [
                'landingPage' => $landingPage
            ]
        ]);
    }

    /****
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $landingPage = $this->_mainModel->find($id);

        $landingPage->delete();

        return response()->json([
            'message' => 'delete success',
            'data' => [
            ]
        ]);
    }
}
