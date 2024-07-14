<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiPartner;
use Illuminate\Http\Request;

class ApiPartnersController extends Controller
{
    /***
     * @var ApiPartner
     */
    private $_mainModel;

    public function __construct(ApiPartner $partner)
    {

        $this->_mainModel = $partner;
    }

    public function index(Request $request)
    {

        $filter = $request->all();

        $query = $this->_mainModel;


        if (isset($filter['id'])) {
            if (is_array($filter['id'])) {
                $query = $query->whereIn('id', $filter['id']);
            } else {
                $query = $query->where('id', $filter['id']);
            }
        }

        if (isset($filter['code'])) {
            if (is_array($filter['code'])) {
                $query = $query->whereIn('code', $filter['code']);
            } else {
                $query = $query->where('code', $filter['code']);
            }
        }

        $list = $query->paginate($request->get('limit', 20));

        return response()->json([
            'message' => 'get success',
            'data' => [
                'apiPartners' => $list
            ]
        ]);
    }
}
