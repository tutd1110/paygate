<?php

namespace App\Http\Controllers\Api\PGW;

use App\Http\Controllers\Controller;
use App\Http\Requests\PGW\PgwPartnerRegistriBankingRequest;
use App\Models\PGW\PgwPartnerRegistriBanking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PgwPartnerRegistriBankingController extends Controller
{
    public function __construct(PgwPartnerRegistriBanking $pgwPartnerRegistriBanking)
    {
        $this->pgwPartnerRegistriBanking = $pgwPartnerRegistriBanking;
    }

    public function index(Request $request)
    {
        $filter = $request->all();
        $query = $this->pgwPartnerRegistriBanking::query();
        $query->with('bankingList');
        if (isset($filter['id'])) {
            $query = $query->where('id', $filter['id']);
        }
        if (isset($filter['code'])) {
            $query = $query->where('code', $filter['code']);
        }
        if (isset($filter['partner_code'])) {
            $query = $query->where('partner_code', $filter['partner_code']);
        }
        if (isset($filter['owner'])) {
            $query = $query->where('owner', 'like', '%' . $filter['owner'] . '%');
        }
        if (isset($filter['bank_number'])) {
            $query = $query->where('bank_number', $filter['bank_number']);
        }
        if (isset($filter['type'])) {
            $query = $query->where('type', $filter['type']);
        }
        $pgwPartnerRegistriBanking = $query
            ->paginate($request->get('limit', config('cms.limit')));
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'pgwPartnerRegistriBanking' => $pgwPartnerRegistriBanking,
            ]
        ]);
    }


    public function store(PgwPartnerRegistriBankingRequest $request)
    {
        $filter = $request->validated();
        DB::beginTransaction();
        try {
            if ($filter) {
                $pgwPartnerRegistriBanking = $this->pgwPartnerRegistriBanking->create($filter);
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'success',
                    'data' => [
                        'pgwPartnerRegistriBanking' => $pgwPartnerRegistriBanking,
                    ]
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack(
            );
            return response()->json([
                'data' => [
                    'message' => $e->getMessage(),
                    'type' => 'error',
                ]
            ]);
        }
    }


    public function update(PgwPartnerRegistriBankingRequest $request, $id)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $pgwPartnerRegistriBanking = $this->pgwPartnerRegistriBanking::find($id);
            $pgwPartnerRegistriBanking->fill($data);
            $pgwPartnerRegistriBanking->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'update success',
                'data' => [
                    'pgwPartnerRegistriBanking' => $pgwPartnerRegistriBanking
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
            $pgwPartnerRegistriBanking = $this->pgwPartnerRegistriBanking->find($id);
            $pgwPartnerRegistriBanking->delete();
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
