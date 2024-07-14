<?php

namespace App\Http\Controllers\Api\PGW;

use App\Http\Controllers\Controller;
use App\Http\Requests\PGW\PgwPartnerRequest;
use App\Models\PGW\PgwBankingList;
use App\Models\PGW\PgwPartner;
use App\Models\PGW\PgwPaymentMerchants;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PgwPartnerController extends Controller
{
    /***
     * @var PgwPartner
     */
    private $pgwPartner;

    public function __construct(PgwPartner $pgwPartner, PgwBankingList $pgwBankingList, PgwPaymentMerchants $pgwPaymentMerchant)
    {
        $this->pgwPartner = $pgwPartner;
        $this->pgwBankingList = $pgwBankingList;
        $this->pgwPaymentMerchant = $pgwPaymentMerchant;

    }

    public function index(Request $request)
    {
        $filter = $request->all();
        $query = $this->pgwPartner::query();
        $query->with('registriBanking')->with('resgistriMerchant');
        if (isset($filter['id'])) {
            $query = $query->where('id', $filter['id']);
        }
        if (isset($filter['name'])) {
            if (is_array($filter['name'])) {
                $query = $query->whereIn('name', 'like', '%' . $filter['name'] . '%');
            } else {
                $query = $query->where('name', 'like', '%' . $filter['name'] . '%');
            }
        }
        if (isset($filter['code'])) {
            if (is_array($filter['code'])) {
                $query = $query->whereIn('code', $filter['code']);
            } else {
                $query = $query->where('code', $filter['code']);
            }
        }
        if (isset($filter['payment_merchant_id'])) {
            $query = $query->whereHas('resgistriMerchant', function ($query) use ($filter) {
                if (is_array($filter['id_payment_merchant'])) {
                    $query->whereIn('payment_merchant_id', $filter['payment_merchant_id']);
                } else {
                    $query->where('payment_merchant_id', $filter['payment_merchant_id']);
                }
            });
        }
        if (isset($filter['banking_list_id'])) {
            $query = $query->whereHas('registriBanking', function ($query) use ($filter) {
                if (is_array($filter['banking_list_id'])) {
                    $query->whereIn('banking_list_id', $filter['banking_list_id']);
                } else {
                    $query->where('banking_list_id', $filter['banking_list_id']);
                }
            });
        }
        if (isset($filter['bank_number'])) {
            $query = $query->whereHas('registriBanking', function ($query) use ($filter) {
                if (is_array($filter['bank_number'])) {
                    $query->whereIn('bank_number', $filter['bank_number']);
                } else {
                    $query->where('bank_number', $filter['bank_number']);
                }
            });
        }
        if (isset($filter['owner'])) {
            $query = $query->whereHas('registriBanking', function ($query) use ($filter) {
                if (is_array($filter['owner'])) {
                    $query->whereIn('owner', $filter['owner']);
                } else {
                    $query->where('owner', $filter['owner']);
                }
            });
        }
        if (isset($filter['order'])) {
            if (is_array($filter['order'])) {
                foreach ($filter['order'] as $key => $value) {
                    $query = $query->orderBy($value, $filter['direction'][$key] ?? 'asc');
                }
            } else {
                $query = $query->orderBy($filter['order'], $filter['direction'] ?? 'asc');
            }
        }
        if (isset($filter['order_by'])){
            $query = $query->orderBy('id','desc');
        }
        $listPartner = $query->get();
        $listBankingList = $this->pgwBankingList->get();
        $listPaymentMerchant = $this->pgwPaymentMerchant->get();

        /** Liên kết giữa bảng pgw_banking_lists với bảng pgw_registri_bankings **/
        $bankingList_arr = [];
        foreach ($listBankingList as $bankingList) {
            $bankingList_arr[$bankingList->id] = $bankingList;
        }
        foreach ($listPartner as $itemPartner) {
            if ($itemPartner['registriBanking']) {
                foreach ($itemPartner['registriBanking'] as $key => $itemBankings) {
                    $itemPartner['registriBanking'][$key]['banking_list'] = [];
                    if (isset($bankingList_arr[$itemBankings['banking_list_id']])) {
                        $itemPartner['registriBanking'][$key]['banking_list'] = $bankingList_arr[$itemBankings['banking_list_id']];
                    }
                }
            }
        }

        /** Liên kết giữa bảng pgw_payment_mechart với bảng pgw_registri_payment_merchants **/
        $paymentMerchantList_arr = [];
        foreach ($listPaymentMerchant as $paymentMerchantList) {
            $paymentMerchantList_arr[$paymentMerchantList->id] = $paymentMerchantList;
        }
        foreach ($listPartner as $itemPartner) {
            if ($itemPartner['resgistriMerchant']) {
                foreach ($itemPartner['resgistriMerchant'] as $key => $itemMerchants) {
                    $itemPartner['resgistriMerchant'][$key]['payment_merchant_list'] = [];
                    if (isset($paymentMerchantList_arr[$itemMerchants['payment_merchant_id']])) {
                        $itemPartner['resgistriMerchant'][$key]['payment_merchant_list'] = $paymentMerchantList_arr[$itemMerchants['payment_merchant_id']];
                    }
                }
            }
        }
        if (isset($filter['get_all'])) {
            $pgwPartner = $listPartner;
        }else {
            $pgwPartner = $this->paginateArray($listPartner, $request->get('limit', config('cms.limit')));
        }
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'pgwPartner' => $pgwPartner,
            ]
        ]);
    }

    public function store(PgwPartnerRequest $request)
    {
        $filter = $request->validated();
        DB::beginTransaction();
        try {
            $filter['code'] = strtoupper($filter['code']);
            if ($filter) {
                $pgwPartner = $this->pgwPartner->create($filter);
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'success',
                    'data' => [
                        'pgwPartner' => $pgwPartner,
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
        //
    }

    public function update(PgwPartnerRequest $request, $id)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
//            $data['code'] = strtoupper($data['code']);
            $pgwPartner = $this->pgwPartner::find($id);
            $pgwPartner->fill($data);
            $pgwPartner->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'update success',
                'data' => [
                    'pgwPartner' => $pgwPartner
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
            $pgwPartner = $this->pgwPartner->find($id);
            $pgwPartner->delete();
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

    public function paginateArray($items, $perPage, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $current_page_orders = array_slice($items->toArray(), ($page - 1) * $perPage, $perPage);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($current_page_orders, count($items->toArray()), $perPage, $page, $options);
    }
}

