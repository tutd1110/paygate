<?php

namespace App\Http\Controllers\Api\SYS;

use App\Http\Controllers\Controller;
use App\Http\Requests\SYS\SysGroupRequest;
use App\Models\SYS\SysGroup;
use App\Models\SYS\SysPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SysGroupController extends Controller
{
    public function __construct(SysGroup $sysGroup, SysPermission $sysPermission)
    {
        $this->sysGroup = $sysGroup;
        $this->sysPermission = $sysPermission;
    }

    public function index(Request $request)
    {
        $filter = $request->all();
        $query = $this->sysGroup::query()->with('sysGroupPermission');
        $listGroups = $query->get();
        $listPermission = $this->sysPermission->get();
        if (isset($filter['id'])) {
            $query = $query->where('id', $filter['id']);
        }
        if (isset($filter['partner_code'])) {
            if (is_array($filter['partner_code'])) {
                $query = $query->whereIn('partner_code', $filter['partner_code']);
            } else {
                $query = $query->where('partner_code', $filter['partner_code']);
            }
        }
        if (isset($filter['name'])) {
            if (is_array($filter['name'])) {
                $query = $query->whereIn('name', 'like', '%' . $filter['name'] . '%');
            } else {
                $query = $query->where('name', 'like', '%' . $filter['name'] . '%');
            }
        }
        if (isset($filter['status'])) {
            if (is_array($filter['status'])) {
                $query = $query->whereIn('status', $filter['status']);
            } else {
                $query = $query->where('status', $filter['status']);
            }
        }
        if (isset($filter['order_by'])) {
            if (is_array($filter['order_by'])) {
                foreach ($filter['order_by'] as $key => $value) {
                    $query = $query->orderBy($value, $filter['direction'][$key] ?? 'asc');
                }
            } else {
                $filter['order_by'] = explode(',', $filter['order_by']);
                $filter['direction'] = explode(',', $filter['direction']);
                foreach ($filter['order_by'] as $key => $value) {
                    $query = $query->orderBy($value, $filter['direction'][$key] ?? 'asc');
                }
            }
        }
        if (isset($filter['permission'])) {
            /** Liên kết giữa bảng sysPermission với bảng sysGroups **/
            $permissionList_arr = [];
            foreach ($listPermission as $permission) {
                $permissionList_arr[$permission->id] = $permission;
            }
            $listGroupsPermissions = $query->get();

            if (count($listGroupsPermissions) != 0) {
                foreach ($listGroupsPermissions as $key => $groups) {
                    if ($groups['sysGroupPermission']) {
                        foreach ($groups['sysGroupPermission'] as $key => $itemPermission) {
                            $groups['sysGroupPermission'][$key]['permission_list'] = [];
                            if (isset($permissionList_arr[$itemPermission['permission_id']])) {
                                $groups['sysGroupPermission'][$key]['permission_list'] = $permissionList_arr[$itemPermission['permission_id']];
                            }
                        }
                    }
                    return response()->json([
                        'status' => true,
                        'message' => 'success',
                        'data' => [
                            'sysGroup' => $groups['sysGroupPermission'],
                        ]
                    ]);
                }



            }
        }

        $sysGroup = $query
            ->paginate($request->get('limit', config('cms.limit')));
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'sysGroup' => $sysGroup,
            ]
        ]);
    }

    public function store(SysGroupRequest $request)
    {
        $filter = $request->validated();
        DB::beginTransaction();
        try {
            $filter['partner_code'] = strtoupper($filter['partner_code'] ?? '');
            if ($filter) {
                $sysGroup = $this->sysGroup->create($filter);
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'success',
                    'data' => [
                        'sysGroup' => $sysGroup,
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

    public function update(SysGroupRequest $request, $id)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $sysGroup = $this->sysGroup::find($id);
            $sysGroup->fill($data);
            $sysGroup->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'update success',
                'data' => [
                    'sysGroup' => $sysGroup
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
            $sysGroup = $this->sysGroup::find($id);
            $sysGroup->delete();
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
