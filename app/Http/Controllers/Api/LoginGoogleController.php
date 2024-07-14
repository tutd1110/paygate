<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginGoogleRequest;
use App\Models\SYS\SysGroup;
use App\Models\SYS\SysUserGroup;
use App\Models\ThirdParty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class LoginGoogleController extends Controller
{
    /***
     * @var User
     */
    private $user;

    public function __construct(User $user, SysUserGroup $sysUserGroup)
    {
        $this->user = $user;
        $this->sysUserGroup = $sysUserGroup;
    }

    public function index(Request $request)
    {
        try {
            $filter = $request->all();
            $filter['email'] = Crypt::decryptString($filter['email']);
            $query = $this->user::query()->where('status', '!=', 'deleted');
            if (isset($filter['email'])) {
                $t = ThirdParty::where('key', 'insert_contact')->first();
                $access_token = auth('third_party')->claims(['token' => $t->token])->login($t);
                $query = $query->with('LandingPage')->where('email', $filter['email']);
                $query = $query->with('groups')->where('email', $filter['email']);
                $user = $query->first();
                $group = $this->sysUserGroup::query()->where('user_id', $user['id'])->with('permissionGroupRoute')->get()->toArray();
                $array_group = [];
                $array_route = [];
                foreach ($group as $key => $value) {
                    $array_group = array_merge($array_group, $value['permission_group_route']);
                }
                foreach ($array_group as $key => $value) {
                    array_push($array_route, $value['router']);
                }
                $array_route = array_unique($array_route);
                $user['permission_group_route'] = $array_route;
                if ($user) {
                    $user['access_token'] = $access_token;
                }
                return response()->json([
                    'status' => true,
                    'message' => 'success',
                    'data' => [
                        'user' => $user,
                    ]
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'error',
                ]);
            }
        } catch (\Exception $e) {
            return [
                'message' => 'Đăng nhập thất bại',
                'error' => $e->getMessage()
            ];
        }

    }

//    public function store(LoginGoogleRequest $request)
//    {
//        $t = ThirdParty::where('key','insert_contact')->first();
//        $access_token = auth('third_party')->claims(['token' => $t->token])->login($t);
//        $filter = $request->validated();
//        if ($filter) {
//            $user = $this->user->create($filter);
//            $user['access_token'] = $access_token;
//            return response()->json([
//                'status'=>true,
//                'message' => 'success',
//                'data' => [
//                    'user' => $user,
//                ]
//            ]);
//        }
//    }

}
