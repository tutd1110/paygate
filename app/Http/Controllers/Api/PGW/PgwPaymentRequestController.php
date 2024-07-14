<?php

namespace App\Http\Controllers\Api\PGW;

use App\Http\Controllers\Controller;
use App\Http\Requests\PGW\PgwPaymentRequestRequest;
use App\Models\PGW\PgwPaymentRequest;
use Illuminate\Http\Request;

class PgwPaymentRequestController extends Controller
{
    public function __construct(PgwPaymentRequest $pgwPaymentRequest)
    {
        $this->pgwPaymentRequest = $pgwPaymentRequest;
    }

    public function index(Request $request)
    {
        $filter = $request->all();
        $query = $this->pgwPaymentRequest::query()->with('partner')->with('merchant')->with('banking');
        if (isset($filter['id'])) {
            $query = $query->where('id', $filter['id']);
        }
        if (isset($filter['order_client_id'])) {
            $query = $query->where('order_client_id', $filter['order_client_id']);
        }
        if (isset($filter['merchant_id'])) {
            $query = $query->where('merchant_id', $filter['merchant_id']);
        }
        if (isset($filter['banking_id'])) {
            $query = $query->where('banking_id', $filter['banking_id']);
        }
        if (isset($filter['payment_code'])) {
            $query = $query->where('payment_code', $filter['payment_code']);
        }
        if (isset($filter['transsion_id'])) {
            $query = $query->where('transsion_id', $filter['transsion_id']);
        }
        if (isset($filter['partner_code'])) {
            $query = $query->where('partner_code', $filter['partner_code']);
        }
        if (isset($filter['paid_status'])) {
            $query = $query->where('paid_status', $filter['paid_status']);
        }
        if (isset($filter['start_date'])) {
            $query = $query->where('created_at', '>', $filter['start_date']);
        }
        if (isset($filter['end_date'])) {
            $query = $query->where('created_at', '<', $filter['end_date']);
        }
        if (isset($filter['order'])) {
            if (is_array($filter['order'])) {
                foreach ($filter['order'] as $key => $value) {
                    $query = $query->orderBy($value, $filter['direction'][$key] ?? 'asc');
                }
            } else {
                $filter['order'] = explode(',', $filter['order']);
                $filter['direction'] = explode(',', $filter['direction']);

                foreach ($filter['order'] as $key => $value) {
                    $query = $query->orderBy($value, $filter['direction'][$key] ?? 'asc');
                }
            }
        }
        $pgwPaymentRequest = $query->orderByDesc('id');
        $pgwPaymentRequest = $query
            ->paginate($request->get('limit', config('cms.limit')));
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'pgwPaymentRequest' => $pgwPaymentRequest,
            ]
        ]);
    }

    public function store(PgwPaymentRequestRequest $request)
    {
        $filter = $request->validated();
        $filter['partner_code'] = strtoupper($filter['partner_code']);
        if ($filter) {
            $pgwPartnerRegistriBanking = $this->pgwPartnerRegistriBanking->create($filter);
            return response()->json([
                'status'=>true,
                'message' => 'success',
                'data' => [
                    'pgwPartnerRegistriBanking' => $pgwPartnerRegistriBanking,
                ]
            ]);
        }
    }
    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
