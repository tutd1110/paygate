<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LandingPageInfo\LangdingPageInfoRequest;
use App\Http\Requests\LandingPageInfo\ListLandingPageInfo;
use App\Models\LandingPageInfo;
use Illuminate\Http\Request;

class LandingPageInfoController extends Controller
{
    public function __construct(LandingPageInfo $landing_page_infos){
        $this->landing_page_infos = $landing_page_infos;
    }

    public function index(ListLandingPageInfo $request)
    {
        $query = $this->landing_page_infos;
        $filter = $request->all();

        if (isset($filter['id'])) {
            if (is_array($filter['id'])) {
                $query = $query->whereIn('id', $filter['id']);
            } else {
                $query = $query->where('id', $filter['id']);
            }
        }

        if (isset($filter['landing_page_id'])) {
            if (is_array($filter['landing_page_id'])) {
                $query = $query->whereIn('landing_page_id', $filter['landing_page_id']);
            } else {
                $query = $query->where('landing_page_id', $filter['landing_page_id']);
            }
        }

        if ($request->get('count')) {
            $infos = $query->paginate($request->get('limit', 20));
        } else {
            $infos = $query->simplePaginate($request->get('limit', 20));
        }

        return response()->json([
            'message' => 'get success',
            'data' => [
                'infos' => $infos
            ]
        ]);
    }


    public function store(LangdingPageInfoRequest $request)
    {
        $infos = $this->landing_page_infos->create($request->all());
        return response()->json([
            'message' => 'save success',
            'data' => [
                'infos' => $infos
            ]
        ]);
    }


    public function show($id)
    {
        $infos = $this->landing_page_infos->find($id);

        return response()->json([
            'message' => 'get success',
            'data' => [
                'infos' => $infos
            ]
        ]);
    }

    public function update(LangdingPageInfoRequest $request, $id)
    {
        $infos =$this->landing_page_infos->find($id);

        $infos->fill($request->validated());
        $infos->update($request->all());
        return response()->json([
            'message' => 'update success',
            'data' => [
                'infos' => $infos
            ]
        ]);
    }

    public function destroy($id)
    {
        $infos = $this->landing_page_infos->find($id);

        $infos->delete();

        return response()->json([
            'message' => 'delete success',
            'data' => [
            ]
        ]);
    }
}
