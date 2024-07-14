<?php

namespace App\Http\Controllers\Api\PGW;

use App\Http\Controllers\Controller;
use App\Http\Requests\PGW\PgwPartnerResgistriMerchantRequest;
use App\Models\PGW\PgwPartnerResgistriMerchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PgwPartnerResgistriMerchantController extends Controller
{
    /***
     * @var PgwPartnerResgistriMerchant
     */
    private $pgwPartnerResgistriMerchant;

    public function __construct(PgwPartnerResgistriMerchant $pgwPartnerResgistriMerchant)
    {
        $this->pgwPartnerResgistriMerchant = $pgwPartnerResgistriMerchant;

    }

    public function index(Request $request)
    {
        try {
            $filter = $request->all();
            $query = $this->pgwPartnerResgistriMerchant::query();
            if (isset($filter['id'])) {
                $query = $query->where('id', $filter['id']);
            }
            if (isset($filter['partner_code'])) {
                if (is_array($filter['partner_code'])) {
                    $query->whereIn('partner_code', $filter['partner_code']);
                } else {
                    $query->where('partner_code', $filter['partner_code']);
                }
            }
            if (isset($filter['merchant'])) {
                $query->with('merchant');
            }
            $pgwPartnerResgistriMerchant = $query
                ->paginate($request->get('limit', config('cms.limit')));
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => [
                    'pgwPartnerResgistriMerchant' => $pgwPartnerResgistriMerchant,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'data' => [
                    'message' => $e->getMessage(),
                    'type' => 'error',
                ]
            ]);
        }
    }


    public function store(PgwPartnerResgistriMerchantRequest $request)
    {
        $filter = $request->validated();
        DB::beginTransaction();
        try {
            $pgwPartnerResgistriMerchant = [];
            $filter['partner_code'] = strtoupper($filter['partner_code']);
            if ($filter) {
                foreach ($filter['payment_merchant_id'] as $key => $paymentMerchantID) {
                    $param = [
                        'partner_code' => $filter['partner_code'],
                        'payment_merchant_id' => $paymentMerchantID,
                        'business' => $filter['business'][$key]
                    ];
                    array_push($pgwPartnerResgistriMerchant, $param);
                }
                $this->pgwPartnerResgistriMerchant->insert($pgwPartnerResgistriMerchant);
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'success',
                    'data' => [
                        'pgwPartnerResgistriMerchant' => $pgwPartnerResgistriMerchant,
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


    public function update(PgwPartnerResgistriMerchantRequest $request, $id)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $data['partner_code'] = strtoupper($data['partner_code']);

            $pgwPartnerResgistriMerchant = $this->pgwPartnerResgistriMerchant::find($id);
            $pgwPartnerResgistriMerchant->fill($data);
            $pgwPartnerResgistriMerchant->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'update success',
                'data' => [
                    'pgwPartnerResgistriMerchant' => $pgwPartnerResgistriMerchant
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
            $pgwPartnerResgistriMerchant = $this->pgwPartnerResgistriMerchant->find($id);
            $pgwPartnerResgistriMerchant->delete();
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
