<?php

namespace App\Http\Controllers\Api\SYS;

use App\Http\Controllers\Controller;
use App\Http\Requests\SYS\SysUserGroupRequest;
use App\Models\SYS\SysPermission;
use App\Models\SYS\SysUserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SysUserGroupController extends Controller
{
    public function __construct(SysUserGroup $sysUserGroup, SysPermission $sysPermission)
    {
        $this->sysUserGroup = $sysUserGroup;
        $this->sysPermission = $sysPermission;
    }

    public function index(Request $request)
    {

        $filter = $request->all();
        $query = $this->sysUserGroup::query()->with('permissionGroup');;
        $listUserGroup = $query->get();
        $listPermission = $this->sysPermission->get();
        if (isset($filter['id'])) {
            $query = $query->where('id', $filter['id']);
        }
        if (isset($filter['user_id'])) {
            $query = $query->where('user_id', $filter['user_id']);
        }
        if (isset($filter['group_id'])) {
            $query = $query->where('group_id', $filter['group_id']);
        }
        if (isset($filter['permission'])) {
            /** Liên kết giữa bảng sysPermission với bảng sysUserGroup **/
            $permissionList_arr = [];
            foreach ($listPermission as $permission) {
                $permissionList_arr[$permission->id] = $permission;
            }
            $listGroupsPermissions = $query->get();
            if (count($listGroupsPermissions) != 0) {
                foreach ($listGroupsPermissions as $key => $userGroup) {
                    if ($userGroup['permissionGroup']) {
                        foreach ($userGroup['permissionGroup'] as $key => $itemPermission) {
                            $userGroup['permissionGroup'][$key]['permission_list'] = [];
                            if (isset($permissionList_arr[$itemPermission['permission_id']])) {
                                $userGroup['permissionGroup'][$key]['permission_list'] = $permissionList_arr[$itemPermission['permission_id']];
                            }
                        }
                    }
                }
                return response()->json([
                    'status' => true,
                    'message' => 'success',
                    'data' => [
                        'sysUserGroup' => $listGroupsPermissions,
                    ]
                ]);
            }
        }
        $sysUserGroup = $query
            ->paginate($request->get('limit', config('cms.limit')));
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'sysUserGroup' => $sysUserGroup,
            ]
        ]);
    }

    public function store(SysUserGroupRequest $request)
    {

        $filter = $request->validated();
        $sysUserGroups = [];
        DB::beginTransaction();
        try {
            if ($filter) {
                foreach ($filter['user_group'] as $key => $user_group) {
                    $param = [
                        'user_id' => $user_group['user_id'],
                        'group_id' => $user_group['group_id'],
                        'created_by' => $filter['created_by'] ?? null,
                        'updated_by' => $filter['updated_by'] ?? null,
                    ];
                    array_push($sysUserGroups, $param);
                }
                $this->sysUserGroup->insert($sysUserGroups);
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'success',
                    'data' => [
                        'sysUserGroup' => $sysUserGroups,
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

    public function update(SysUserGroupRequest $request, $id)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $sysUserGroup = $this->sysUserGroup::find($id);
            $sysUserGroup->fill($data);
            $sysUserGroup->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'update success',
                'data' => [
                    'sysUserGroup' => $sysUserGroup
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

    public function destroy(SysUserGroupRequest $request, $id)
    {
        $filter = $request->validated();
        DB::beginTransaction();
        try {
            $groupIdDelete = [];
            $userIdDelete = [];
            foreach ($filter['user_group'] as $key => $user_group) {
                array_push($groupIdDelete, $user_group['group_id']);
                array_push($userIdDelete, $user_group['user_id']);
            }
            $this->sysUserGroup
                ->whereIn('group_id', $groupIdDelete)
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
