<?php

namespace App\Http\Controllers\Api\PGW;

use App\Http\Controllers\Controller;
use App\Http\Requests\PGW\PgwOrderRefundRequest;
use App\Models\PGW\PgwOrderRefund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PgwOrderRefundController extends Controller
{
    /***
     * @var PgwOrderRefund
     */
    private $pgwOrderRefund;

    public function __construct(PgwOrderRefund $pgwOrderRefund)
    {
        $this->pgwOrderRefund = $pgwOrderRefund;

    }

    public function index(Request $request)
    {
        $filter = $request->all();
        $query = $this->pgwOrderRefund::query()->with('partner')->with('landingPage')->with('Order');
        if (isset($filter['id'])) {
            $query = $query->where('id', $filter['id']);
        }
        if (isset($filter['landing_page_id'])) {
            if (is_array($filter['landing_page_id'])) {
                $query = $query->whereIn('landing_page_id', $filter['landing_page_id']);
            } else {
                $query = $query->where('landing_page_id', $filter['landing_page_id']);
            }
        }
        if (isset($filter['code'])) {
            $query = $query->whereHas('Order', function ($query) use ($filter) {
                if (is_array($filter['code'])) {
                    $query->whereIn('code', 'like', '%'.$filter['code'] . '%');
                } else {
                    $query->where('code', 'like', '%'.$filter['code'] . '%');
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
        if (empty($filter['export'])) {
            $pgwOrderRefund = $query
                ->paginate($request->get('limit', config('cms.limit')));
        }
        if (isset($filter['export'])) {
            $pgwOrderRefund = $query->get();
        }
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'pgwOrderRefund' => $pgwOrderRefund,
            ]
        ]);
    }

    public function store(PgwOrderRefundRequest $request)
    {
        $filter = $request->validated();
        DB::beginTransaction();
        try {
            $checkOrderRefund = $this->pgwOrderRefund->where('order_id', $request->order_id)->first();
            if ($filter && !$checkOrderRefund) {
                $pgwOrderRefund = $this->pgwOrderRefund->create($filter);
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'success',
                    'data' => [
                        'pgwOrderRefund' => $pgwOrderRefund,
                    ]
                ]);
            } else if ($filter && $checkOrderRefund) {
                $checkOrderRefund['refund_value'] = $filter['refund_value'] + $checkOrderRefund['refund_value'];
                $checkOrderRefund->save();
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'success',
                    'data' => [
                        'pgwOrderRefund' => $checkOrderRefund,
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


    public function update(PgwOrderRefundRequest $request, $id)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $pgwOrderRefund = $this->pgwOrderRefund::find($id);
            $pgwOrderRefund->fill($data);
            $pgwOrderRefund->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'update success',
                'data' => [
                    'pgwOrderRefund' => $pgwOrderRefund
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
            $pgwOrderRefund = $this->pgwOrderRefund->find($id);
            $pgwOrderRefund->delete();
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
