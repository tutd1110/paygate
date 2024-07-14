<?php

namespace App\Http\Controllers\Api\SYS;

use App\Http\Controllers\Controller;
use App\Http\Requests\SYS\SysUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SysUserController extends Controller
{
    public function __construct(User $sysUser)
    {
        $this->sysUser = $sysUser;
    }

    public function index(Request $request)
    {
        $filter = $request->all();
        $query = $this->sysUser::query()->where('status', '!=', 'deleted')->with('groups');

        if (isset($filter['get_landing_page'])) {
            $query =$query->with('LandingPage');
        }
        if (isset($filter['id'])) {
            $query = $query->where('id', $filter['id']);
        }
        if (isset($filter['group_id'])) {
            $query = $query->whereHas('groups', function ($query) use ($filter) {
                if (is_array($filter['group_id'])) {
                    $query->whereIn('group_id', $filter['group_id']);
                } else {
                    $query->where('group_id', $filter['group_id']);
                }
            });
        }
        if (isset($filter['landing_page'])) {
            $query = $query->whereHas('LandingPage', function ($query) use ($filter) {
                if (is_array($filter['landing_page'])) {
                    $query->whereIn('landing_page_id', $filter['landing_page']);
                } else {
                    $query->where('landing_page_id', $filter['landing_page']);
                }
            });
        }
        if (isset($filter['partner_code'])) {
            if (is_array($filter['partner_code'])) {
                $query = $query->whereIn('partner_code', $filter['partner_code']);
            } else {
                $query = $query->where('partner_code', $filter['partner_code']);
            }
        }
        if (isset($filter['name_email'])) {
            $query = $query->where('name', 'like', '%' . $filter['name_email'] . '%')
                ->orWhere('email', 'like', '%' . $filter['name_email'] . '%');
        }
        if (isset($filter['name'])) {
            if (is_array($filter['name'])) {
                $query = $query->whereIn('name', 'like', '%' . $filter['name'] . '%');
            } else {
                $query = $query->where('name', 'like', '%' . $filter['name'] . '%');
            }
        }
        if (isset($filter['email'])) {
            if (is_array($filter['email'])) {
                $query = $query->whereIn('email', 'like', '%' . $filter['email'] . '%');
            } else {
                $query = $query->where('email', 'like', '%' . $filter['email'] . '%');
            }
        }
        if (isset($filter['phone'])) {
            if (is_array($filter['phone'])) {
                $query = $query->whereIn('phone', 'like', '%' . $filter['phone'] . '%');
            } else {
                $query = $query->where('phone', 'like', '%' . $filter['phone'] . '%');
            }
        }
        if (isset($filter['address'])) {
            if (is_array($filter['address'])) {
                $query = $query->whereIn('address', 'like', '%' . $filter['address'] . '%');
            } else {
                $query = $query->where('address', 'like', '%' . $filter['address'] . '%');
            }
        }
        if (isset($filter['owner'])) {
            if (is_array($filter['owner'])) {
                $query = $query->whereIn('owner', $filter['owner']);
            } else {
                $query = $query->where('owner', $filter['owner']);
            }
        }
        if (isset($filter['status'])) {
            if (is_array($filter['status'])) {
                $query = $query->whereIn('status', $filter['status']);
            } else {
                $query = $query->where('status', $filter['status']);
            }
        }
        if (isset($filter['start_date'])) {
            $query = $query->where('created_at', '>', $filter['start_date']);
        }
        if (isset($filter['end_date'])) {
            $query = $query->where('created_at', '<', $filter['end_date']);
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
        if (isset($filter['orderBy'])){
            $query = $query->orderBy('id','desc');
        }
        if (isset($filter['get_all'])){
            $sysUser = $query->get();
        }else {
            $sysUser = $query
                ->paginate($request->get('limit', config('cms.limit')));
        }
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'sysUser' => $sysUser,
            ]
        ]);
    }

    public function store(SysUserRequest $request)
    {
        $filter = $request->validated();
        DB::beginTransaction();
        try {
            $users = User::query()->where('name', $filter['name'])->first();
            if (isset($users)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tên tài khoản và mã đối tác đã tồn tại!',
                ]);
            } else {
                $filter['partner_code'] = strtoupper($filter['partner_code'] ?? '');
                if ($filter) {
                    $sysUser = $this->sysUser->create($filter);
                    DB::commit();
                    return response()->json([
                        'status' => true,
                        'message' => 'success',
                        'data' => [
                            'sysUser' => $sysUser,
                        ]
                    ]);
                }
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

    public function update(SysUserRequest $request, $id)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $sysUser = $this->sysUser::find($id);
            $sysUser->fill($data);
            $sysUser->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'update success',
                'data' => [
                    'sysUser' => $sysUser
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
            $sysUser = $this->sysUser::find($id);
            $sysUser['status'] = 'deleted';
            $sysUser->save();
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
