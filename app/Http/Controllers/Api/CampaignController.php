<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Campaign\CampaignFilterRequest;
use App\Http\Requests\Campaign\CampaignRequest;
use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    /***
     * @var Campaign
     */
    public $_mainModel;

    public function __construct(Campaign $campaign)
    {
        $this->_mainModel = $campaign;
    }

    /***
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(CampaignFilterRequest $request)
    {
        $query = $this->_mainModel;
        $filter = $request->all();

        if (isset($filter['id'])) {
            if (is_array($filter['id'])) {
                $query = $query->whereIn('id', $filter['id']);
            } else {
                $query = $query->where('id', $filter['id']);
            }
        }

        if (isset($filter['department_id'])) {
            if (is_array($filter['department_id'])) {
                $query = $query->whereIn('department_id', $filter['department_id']);
            } else {
                $query = $query->where('department_id', $filter['department_id']);
            }
        }

        if (isset($filter['name'])) {
            if (is_array($filter['name'])) {
                $query = $query->whereIn('name', 'like', $filter['name'].'%');
            } else {
                $query = $query->where('name', 'like', $filter['name'].'%');
            }
        }

        if (isset($filter['code'])) {
            if (is_array($filter['code'])) {
                $query = $query->whereIn('code', $filter['code']);
            } else {
                $query = $query->where('code', $filter['code']);
            }
        }

        if (isset($filter['is_delete'])) {
            if (is_array($filter['is_delete'])) {
                $query = $query->whereIn('is_delete', $filter['is_delete']);
            } else {
                $query = $query->where('is_delete', $filter['is_delete']);
            }
        }

        if (isset($filter['is_active'])) {
            if (is_array($filter['is_active'])) {
                $query = $query->whereIn('is_active', $filter['is_active']);
            } else {
                $query = $query->where('is_active', $filter['is_active']);
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

        if ($request->get('count')) {
            $campaigns = $query->paginate($request->get('limit', 20));
        } else {
            $campaigns = $query->simplePaginate($request->get('limit', 20));
        }

        return response()->json([
            'message' => 'get success',
            'data' => [
                'campaigns' => $campaigns
            ]
        ]);

    }


    /**
     * @param CampaignRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CampaignRequest $request)
    {
        $campaign = $this->_mainModel->create($request->all());

        return response()->json([
            'message' => 'save success',
            'data' => [
                'campaign' => $campaign
            ]
        ]);

    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $campaign = $this->_mainModel->find($id);

        return response()->json([
            'message' => 'get success',
            'data' => [
                'campaign' => $campaign
            ]
        ]);
    }

    /***
     * @param CampaignRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(CampaignRequest $request, $id)
    {
        $campaign = $this->_mainModel->find($id);

        $campaign->fill($request->validated());
        $save = $campaign->save();

        return response()->json([
            'message' => 'update success',
            'data' => [
                'campaign' => $campaign
            ]
        ]);
    }

    /***
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $campaign = $this->_mainModel->find($id);

        $campaign->delete();

        return response()->json([
            'message' => 'delete success',
            'data' => [
            ]
        ]);
    }
}
