<?php

namespace App\Http\Controllers\Api\PGW;

use App\Http\Controllers\Controller;
use App\Http\Requests\PGW\PgwBankingListRequest;
use App\Models\PGW\PgwBankingList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PgwBankingListController extends Controller
{
    public function __construct(PgwBankingList $pgwBankingList)
    {
        $this->pgwBankingList = $pgwBankingList;
    }

    public function index(Request $request)
    {
        $filter = $request->all();
        $query = $this->pgwBankingList::query();
        if (isset($filter['id'])) {
            $query = $query->where('id', $filter['id']);
        }
        if (isset($filter['code'])) {
            $query = $query->where('code', $filter['code']);
        }
        if (isset($filter['name'])) {
            $query = $query->where('name', 'like', '%' . $filter['name'] . '%');
        }
        if (isset($filter['status'])) {
            $query = $query->where('status', $filter['status']);
        }
        if (isset($filter['get_all'])) {
            $query = $query->get();
            $pgwBankingList = [];
            foreach ($query as $key => $value) {
                $pgwBankingList[$value['code']] = $value;
            }
        } else {
            $pgwBankingList = $query->paginate($request->get('limit', config('cms.limit')));
        }
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'pgwBankingList' => $pgwBankingList,
            ]
        ]);
    }


    public function store(PgwBankingListRequest $request)
    {
        DB::beginTransaction();
        $filter = $request->validated();
        try {
            if ($filter) {
                $pgwBankingList = $this->pgwBankingList->create($filter);
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'success',
                    'data' => [
                        'pgwBankingList' => $pgwBankingList,
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


    public function update(PgwBankingListRequest $request, $id)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $pgwBankingList = $this->pgwBankingList::find($id);
            $pgwBankingList->fill($data);
            $pgwBankingList->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'update success',
                'data' => [
                    'pgwBankingList' => $pgwBankingList
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
            $pgwBankingList = $this->pgwBankingList->find($id);
            $pgwBankingList->delete();
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
