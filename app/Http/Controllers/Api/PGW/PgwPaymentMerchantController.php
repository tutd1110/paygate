<?php

namespace App\Http\Controllers\Api\PGW;

use App\Http\Controllers\Controller;
use App\Http\Requests\PGW\PgwPaymentMerchantRequest;
use App\Models\PGW\PgwPaymentMerchants;
use Illuminate\Http\Request;

class PgwPaymentMerchantController extends Controller
{
    /***
     * @var PgwPaymentMerchants
     */
    private $pgwPaymentMerchant;

    public function __construct(PgwPaymentMerchants $pgwPaymentMerchant)
    {
        $this->pgwPaymentMerchant = $pgwPaymentMerchant;

    }

    public function index(Request $request)
    {
        $filter = $request->all();
        $query = $this->pgwPaymentMerchant::query();
        if (isset($filter['id'])) {
            $query = $query->where('id', $filter['id']);
        }
        if(isset($filter['name'])){
            if (is_array($filter['name'])) {
                $query->whereIn('name', 'like', '%'.$filter['name'] . '%');
            } else {
                $query->where('name', 'like', '%'.$filter['name'] . '%');
            }
        }
        if(isset($filter['code'])){
            if (is_array($filter['code'])) {
                $query->whereIn('code', $filter['code']);
            } else {
                $query->where('code', $filter['code'] );
            }
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
        if (isset($filter['get_all'])) {
            $query = $query->get();
            $pgwPaymentMerchant = [];
            foreach ($query as $key => $value) {
                $pgwPaymentMerchant[$value['code']] = $value;
            }
        } else {
            $pgwPaymentMerchant = $query
                ->paginate($request->get('limit', config('cms.limit')));
        }
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'pgwPaymentMerchant' => $pgwPaymentMerchant,
            ]
        ]);
    }


    public function store(PgwPaymentMerchantRequest $request)
    {
        $filter = $request->validated();
        if ($filter) {
            $pgwPaymentMerchant = $this->pgwPaymentMerchant->create($filter);
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => [
                    'pgwPaymentMerchant' => $pgwPaymentMerchant,
                ]
            ]);
        }
    }

    public function update(PgwPaymentMerchantRequest $request, $id)
    {
        $data = $request->validated();

        $pgwPaymentMerchant = $this->pgwPaymentMerchant::find($id);
        $pgwPaymentMerchant->fill($data);
        $pgwPaymentMerchant->save();

        return response()->json([
            'status' => true,
            'message' => 'update success',
            'data' => [
                'pgwPaymentMerchant' => $pgwPaymentMerchant
            ]
        ]);
    }

    public function destroy($id)
    {
        $pgwPaymentMerchant = $this->pgwPaymentMerchant->find($id);

        $pgwPaymentMerchant->delete();

        return response()->json([
            'message' => 'delete success',
            'data' => [
            ]
        ]);
    }
}
