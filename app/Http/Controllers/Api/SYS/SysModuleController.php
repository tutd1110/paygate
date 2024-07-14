<?php

namespace App\Http\Controllers\Api\SYS;

use App\Http\Controllers\Controller;
use App\Http\Requests\SYS\SysModuleRequest;
use App\Models\SYS\SysModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SysModuleController extends Controller
{
    public function __construct(SysModule $sysModule)
    {
        $this->sysModule = $sysModule;
    }

    public function index(Request $request)
    {
        $filter = $request->all();
        $query = $this->sysModule::query()->with('sysPermissions');
        if (isset($filter['id'])) {
            $query = $query->where('id', $filter['id']);
        }
        if (isset($filter['module'])) {
            if (is_array($filter['module'])) {
                $query = $query->whereIn('module', 'like', '%' . $filter['module'] . '%');
            } else {
                $query = $query->where('module', 'like', '%' . $filter['module'] . '%');
            }
        }
        if (isset($filter['start_date'])) {
            $query = $query->where('created_at', '>', $filter['start_date']);
        }
        if (isset($filter['end_date'])) {
            $query = $query->where('created_at', '<', $filter['end_date']);
        }
        if (isset($filter['module_alias'])) {
            if (is_array($filter['module_alias'])) {
                $query = $query->whereIn('module_alias', 'like', '%' . $filter['module_alias'] . '%');
            } else {
                $query = $query->where('module_alias', 'like', '%' . $filter['module_alias'] . '%');
            }
        }
        if (isset($filter['scan_module'])) {
            $query = $this->sysModule::query()->select('id', 'module')->get();
            $arrayModule = [];
            foreach ($query as $module) {
                $arrayModule[$module['id']] = $module['module'];
            }
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $arrayModule
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
            $sysModule = $query->get();
        }else {
            $sysModule = $query
                ->paginate($request->get('limit', config('cms.limit')));
        }

        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'sysModule' => $sysModule,
            ]
        ]);
    }

    public function store(SysModuleRequest $request)
    {

        $filter = $request->validated();
        DB::beginTransaction();
        try {
            if ($filter) {
                $sysModule = $this->sysModule->create($filter);
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'success',
                    'data' => [
                        'sysModule' => $sysModule,
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

    public function update(SysModuleRequest $request, $id)
    {

        $data = $request->validated();
        DB::beginTransaction();
        try {
            $sysModule = $this->sysModule::find($id);
            $sysModule->fill($data);
            $sysModule->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'update success',
                'data' => [
                    'sysModule' => $sysModule
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
            $sysModule = $this->sysModule::find($id);
            $sysModule->delete();
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
