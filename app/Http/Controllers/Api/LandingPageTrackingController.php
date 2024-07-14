<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LandingPageTracking\LandingPageTrackingRequest;
use App\Models\LandingPageTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LandingPageTrackingController extends Controller
{
    public function __construct(LandingPageTracking $landingPageTracking)
    {
        $this->landingPageTracking = $landingPageTracking;
    }
    public function index(Request $request)
    {
        $filter = $request->all();
        $query = $this->landingPageTracking::query();
        if (isset($filter['id'])) {
            $query = $query->where('id', $filter['id']);
        }
        if (isset($filter['landing_page_id'])) {
            $query = $query->where('landing_page_id', $filter['landing_page_id']);
        }
        if (isset($filter['header_bottom'])) {
            $query = $query->where('header_bottom', $filter['header_bottom']);
        }
        if (isset($filter['body'])) {
            $query = $query->where('body', $filter['body']);
        }
        if (isset($filter['body_bottom'])) {
            $query = $query->where('body_bottom', $filter['body_bottom']);
        }
        if (isset($filter['footer'])) {
            $query = $query->where('footer', $filter['footer']);
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
        $landingPageTracking = $query
            ->paginate($request->get('limit', config('cms.limit')));
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'landingPageTracking' => $landingPageTracking,
            ]
        ]);
    }

    public function store(LandingPageTrackingRequest $request)
    {
        $filter = $request->validated();
        DB::beginTransaction();
        try {
            if ($filter) {
                $landingPageTracking = $this->landingPageTracking->create($filter);
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'success',
                    'data' => [
                        'landingPageTracking' => $landingPageTracking,
                    ]
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'message' => $e->getMessage(),
                    'type' => 'error',
                ]
            ]);
        }
    }

    public function update(LandingPageTrackingRequest $request, $id)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $landingPageTracking = $this->landingPageTracking::find($id);
            $landingPageTracking->fill($data);
            $landingPageTracking->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'update success',
                'data' => [
                    'landingPageTracking' => $landingPageTracking
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'message' => $e->getMessage(),
                    'type' => 'error',
                ]
            ]);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $landingPageTracking = $this->landingPageTracking::find($id);
            $landingPageTracking->delete();
            DB::commit();
            return response()->json([
                'message' => 'delete success',
                'data' => [
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'message' => $e->getMessage(),
                    'type' => 'error',
                ]
            ]);
        }
    }
}
