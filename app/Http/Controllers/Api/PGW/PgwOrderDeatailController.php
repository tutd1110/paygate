<?php

namespace App\Http\Controllers\Api\PGW;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\PGW\PgwOrderDetailRequest;
use App\Models\PGW\PgwOrderDetail;
use Illuminate\Support\Facades\DB;


class PgwOrderDeatailController extends Controller
{
    public $pgwOrderDetail;
    public function __construct(PgwOrderDetail $pgwOrderDetail)
    {
        $this->pgwOrderDetail = $pgwOrderDetail;
    }
    public function index(Request $request)
    {
        $filter = $request->all();
        $query = $this->pgwOrderDetail::query();
        if (isset($filter['order_id'])) {
            if (is_array($filter['order_id'])) {
                $query = $query->whereIn('order_id', $filter['order_id']);
            } else {
                $query = $query->where('order_id', $filter['order_id']);
            }
        }
        $pgwOrderDetail = $query
        ->paginate($request->get('limit', config('cms.limit')));
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'orderDetail' => $pgwOrderDetail,
            ]
        ]);

    }

    public function update(PgwOrderDetailRequest $request, $id)
    {

        $data = $request->validated();
        DB::beginTransaction();
        try {
            $pgwOrderDetail = $this->pgwOrderDetail::find($id);
            $pgwOrderDetail->fill($data);
            $pgwOrderDetail->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'update success',
                'data' => [
                    'pgwOrderDetail' => $pgwOrderDetail
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
    public function updateMultiple(PgwOrderDetailRequest $request){
        $data = $request->validated();
        DB::beginTransaction();
        try {
            foreach ($data['id'] as $key=>$value){
                $pgwOrderDetail = $this->pgwOrderDetail::find($value);
                $pgwOrderDetail->description = $data['description'][$key];
                $pgwOrderDetail->save();
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'update success',
                'data' => [
                    'pgwOrderDetail' => $pgwOrderDetail
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
    public function paginateArray($items, $perPage, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $current_page_orders = array_slice($items->toArray(), ($page - 1) * $perPage, $perPage);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($current_page_orders, count($items->toArray()), $perPage, $page, $options);
    }

}
