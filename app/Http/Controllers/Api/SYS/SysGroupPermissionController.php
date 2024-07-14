<?php

namespace App\Http\Controllers\Api\SYS;

use App\Http\Controllers\Controller;
use App\Http\Requests\SYS\SysGroupPermissionRequest;
use App\Models\SYS\SysGroupPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SysGroupPermissionController extends Controller
{
    public function __construct(SysGroupPermission $sysGroupPermission)
    {
        $this->sysGroupPermission = $sysGroupPermission;
    }

    public function index(Request $request)
    {
        $filter = $request->all();
        $query = $this->sysGroupPermission::query();
        if (isset($filter['id'])) {
            $query = $query->where('id', $filter['id']);
        }
        if (isset($filter['permission_id'])) {
            $query = $query->where('permission_id', $filter['permission_id']);
        }
        if (isset($filter['group_id'])) {
            $query->with('groupPermisson');
            if (is_array($filter['group_id'])) {
                $query = $query->whereIn('group_id', $filter['group_id']);
            } else {
                $query = $query->where('group_id', $filter['group_id']);

            }
        }
        $sysGroupPermission = $query
            ->paginate($request->get('limit', config('cms.limit')));
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'sysGroupPermission' => $sysGroupPermission,
            ]
        ]);
    }

    public function store(SysGroupPermissionRequest $request)
    {
        $filter = $request->validated();
        $sysGroupPermissions = [];
        DB::beginTransaction();
        try {
            if ($filter) {
                foreach ($filter['group_permission'] as $key => $group_permission) {
                    $param = [
                        'group_id' => $group_permission['group_id'],
                        'permission_id' => $group_permission['permission_id'],
                        'created_by' => $filter['created_by'] ?? null,
                        'updated_by' => $filter['updated_by'] ?? null,
                    ];
                    array_push($sysGroupPermissions, $param);
                }
                $this->sysGroupPermission->insert($sysGroupPermissions);
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'success',
                    'data' => [
                        'sysGroupPermission' => $sysGroupPermissions,
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

    public function update(SysGroupPermissionRequest $request, $id)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $sysGroupPermission = $this->sysGroupPermission::find($id);
            $sysGroupPermission->fill($data);
            $sysGroupPermission->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'update success',
                'data' => [
                    'sysGroupPermission' => $sysGroupPermission
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

    public function destroy(SysGroupPermissionRequest $request, $id)
    {
        $filter = $request->validated();
        DB::beginTransaction();
        try {
            $groupIdDelete = [];
            $permissionIdDelete = [];
            foreach ($filter['group_permission'] as $key => $group_permission) {
                array_push($groupIdDelete, $group_permission['group_id']);
                array_push($permissionIdDelete, $group_permission['permission_id']);
            }
            $this->sysGroupPermission
                ->whereIn('group_id', $groupIdDelete)
                ->whereIn('permission_id', $permissionIdDelete)
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
