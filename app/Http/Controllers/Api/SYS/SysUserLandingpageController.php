<?php

namespace App\Http\Controllers\Api\SYS;

use App\Http\Controllers\Controller;
use App\Http\Requests\SYS\SysUserLandingpageRequest;
use App\Models\SYS\SysUserLandingpage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SysUserLandingpageController extends Controller
{
    public  function __construct(SysUserLandingpage $sysUserLanding)
    {
        $this->sysUserLanding = $sysUserLanding;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = $request->all();
        $query = $this->sysUserLanding::query();
        if (isset($filter['id'])) {
            $query = $query->where('id', $filter['id']);
        }
        if (isset($filter['user_id'])) {
            $query = $query->where('user_id', $filter['user_id']);
        }
        if (isset($filter['landing_page_id'])) {
            $query = $query->where('group_id', $filter['landing_page_id']);
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
        $sysUserLandingPage = $query
            ->paginate($request->get('limit', config('cms.limit')));
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'sysUserLandingPage' => $sysUserLandingPage,
            ]
        ]);
    }
    public function store(SysUserLandingpageRequest $request)
    {
        $filter = $request->validated();
        $sysUserLandingpage = [];
        DB::beginTransaction();
        try {
            if ($filter) {
                foreach ($filter['user_landingpage'] as $key => $user_landingpage) {
                    $param = [
                        'user_id' => $user_landingpage['user_id'],
                        'landing_page_id' => $user_landingpage['landing_page_id'],
                        'created_by' => $filter['created_by'] ?? null,
                        'updated_at' => $filter['updated_by'] ?? null,
                        'updated_by' => $filter['updated_by'] ?? null,
                    ];
                    array_push($sysUserLandingpage, $param);
                }
                $this->sysUserLanding->insert($sysUserLandingpage);
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'success',
                    'data' => [
                        'sysUserLandingpage' => $sysUserLandingpage,
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

    public function update(SysUserLandingpageRequest $request, $id)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $sysUserLanding = $this->sysUserLanding::find($id);
            $sysUserLanding->fill($data);
            $sysUserLanding->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'update success',
                'data' => [
                    'sysUserLanding' => $sysUserLanding
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
    public function destroy(SysUserLandingpageRequest $request,$id)
    {
        $filter = $request->validated();
        DB::beginTransaction();
        try {
            $userIdDelete = [];
            $landingPageIdDelete = [];
            foreach ($filter['user_landingpage'] as $key => $user_landingpage) {
                array_push($landingPageIdDelete, $user_landingpage['landing_page_id']);
                array_push($userIdDelete, $user_landingpage['user_id']);
            }
            $this->sysUserLanding
                ->whereIn('landing_page_id', $landingPageIdDelete)
                ->whereIn('user_id', $userIdDelete)
                ->delete();
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
