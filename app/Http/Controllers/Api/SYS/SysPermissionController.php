<?php

namespace App\Http\Controllers\Api\SYS;

use App\Http\Controllers\Controller;
use App\Http\Requests\SYS\SysPermissionRequest;
use App\Http\Requests\SYS\SysScanPermissionRequest;
use App\Models\SYS\SysModule;
use App\Models\SYS\SysPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SysPermissionController extends Controller
{
    public function __construct(SysPermission $sysPermission, SysModule $sysModule)
    {
        $this->sysPermission = $sysPermission;
        $this->sysModule = $sysModule;
    }

    public function index(Request $request)
    {
        $filter = $request->all();
        $query = $this->sysPermission::query()->with('module');
        if (isset($filter['id'])) {
            $query = $query->where('id', $filter['id']);
        }

        if (isset($filter['module_id'])) {
            if (is_array($filter['module_id'])) {
                $query = $query->whereIn('module_id', $filter['module_id']);
            } else {
                $query = $query->where('module_id', $filter['module_id']);
            }
        }
        if (isset($filter['name'])) {
            if (is_array($filter['name'])) {
                $query = $query->whereIn('name', 'like', '%' . $filter['name'] . '%');
            } else {
                $query = $query->where('name', 'like', '%' . $filter['name'] . '%');
            }
        }
        if (isset($filter['name_alias'])) {
            if (is_array($filter['name_alias'])) {
                $query = $query->whereIn('name_alias', 'like', '%' . $filter['name_alias'] . '%');
            } else {
                $query = $query->where('name_alias', 'like', '%' . $filter['name_alias'] . '%');
            }
        }
        if (isset($filter['start_date'])) {
            $query = $query->where('created_at', '>', $filter['start_date']);
        }
        if (isset($filter['end_date'])) {
            $query = $query->where('created_at', '<', $filter['end_date']);
        }
        if (isset($filter['scan_permission'])) {
            $query = $this->sysPermission::query()->select('id', 'router')->get();
            $arrayPermissions = [];
            foreach ($query as $permission) {
                $arrayPermissions[$permission['id']] = $permission['router'];
            }
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $arrayPermissions
            ]);
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
        if (isset($filter['get_all'])) {
            $sysPermission = $query->get();
        }else {
            $sysPermission = $query
                ->paginate($request->get('limit', config('cms.limit')));
        }

        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'sysPermission' => $sysPermission,
            ]
        ]);
    }

    public function store(SysPermissionRequest $request)
    {
        $filter = $request->validated();
        DB::beginTransaction();
        try {
            if ($filter) {
                $sysPermission = $this->sysPermission->create($filter);
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'success',
                    'data' => [
                        'sysPermission' => $sysPermission,
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

    public function update(SysPermissionRequest $request, $id)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $sysPermission = $this->sysPermission::find($id);
            $sysPermission->fill($data);
            $sysPermission->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'update success',
                'data' => [
                    'sysPermission' => $sysPermission
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

    public function scanPermission(SysScanPermissionRequest $request)
    {
        DB::beginTransaction();
        try {
            $listRouter = $request['listRoutes'];
            $nameModules = $request['nameModules'];
            $listPermissions = $this->sysPermission::query()->select('id', 'router')->get();
            $arrayPermissions = [];
            foreach ($listPermissions as $permission) {
                $arrayPermissions[$permission['id']] = $permission['router'];
            }
            $listModules = $this->sysModule::query()->select('id', 'module')->get();
            $arrayModule = [];
            foreach ($listModules as $module) {
                $arrayModule[$module['id']] = $module['module'];
            }

            /*** Xử lí và lưu Module ***/
            $deleteModule_array = array_diff($arrayModule, $nameModules);
            $deletePermission_array = array_diff($arrayPermissions, $listRouter);
            if (isset($deleteModule_array)) {
                foreach ($deleteModule_array as $key => $moduleDelete) {

                    $this->sysModule::query()->where('id', $key)->delete();
                    $this->sysPermission::query()->where('module_id', $key)->delete();
                }
            }
            if (isset($deletePermission_array)) {
                foreach ($deletePermission_array as $key => $permissionDelete) {
                    $this->sysPermission::query()->where('id', $key)->delete();
                }
            }
            foreach ($nameModules as $module) {
                if (!in_array($module, $arrayModule)) {
                    $param = [
                        'module' => $module,
                        'module_alias' => $module,
                        'created_by' => $request['session_id'],
                        'updated_by' => $request['session_id'],
                    ];
                    $this->sysModule::create($param);
                }
            }

            /** Xử lí và lưu permissions */
            $listModules = $this->sysModule::query()->select('id', 'module')->get();
            $arrayModule = [];
            foreach ($listModules as $module) {
                $arrayModule[$module['id']] = $module['module'];
            }
            foreach ($listRouter as $router) {
                $namePermission = preg_replace('/[^A-Za-z0-9\-]/', '-', $router);
                $namePermission = explode('-', $namePermission);
                $nameModuleAlias = $namePermission[count($namePermission) - 1];
                if ($nameModuleAlias == 'index') {
                    $nameModuleAlias = 'Xem';
                }
                if ($nameModuleAlias == 'store') {
                    $nameModuleAlias = 'Thêm';
                }
                if ($nameModuleAlias == 'update') {
                    $nameModuleAlias = 'Sửa';
                }
                if ($nameModuleAlias == 'destroy') {
                    $nameModuleAlias = 'Xoá';
                }
                $namePermission = explode('Controller', $namePermission[count($namePermission) - 2]);
                $checkModuleID = array_search($namePermission[0], $arrayModule);
                if (isset($checkModuleID)) {
                    $param = [
                        'module_id' => $checkModuleID,
                        'name' => ucfirst($nameModuleAlias) . ' ' . $namePermission[0],
                        'name_alias' => ucfirst($nameModuleAlias) . ' ' . $namePermission[0],
                        'router' => $router,
                        'created_by' => $request['session_id'],
                        'updated_by' => $request['session_id'],
                    ];
                    if (!in_array($router, $arrayPermissions)) {
                        $this->sysPermission::create($param);
                    } else {
                        $id_permission = array_search($router,$arrayPermissions);
                        $sysPermission = $this->sysPermission::find($id_permission);
                        $sysPermission->fill($param);
                        $sysPermission->save();
                    }
                }
            }
            DB::commit();
            return response()->json([
                'data' => [
                    'message' => 'Update success',
                    'type' => 'success',
                ]
            ]);
        } catch (\Throwable $e) {
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
            $query = $this->sysPermission::query();
            $sysPermission = $this->sysPermission::find($id);
            $sysPermission->delete();
            if (isset($filter['module_id'])) {
                $query = $query->where('module_id', $filter['module_id'])->delete();
            }
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
