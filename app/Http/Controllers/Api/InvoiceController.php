<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\InvoiceList;
use App\Http\Requests\Invoice\InvoiceRequest;
use App\Models\ContactLeadProcess;
use App\Models\Department;
use App\Models\Invoice\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index(InvoiceList $request)
    {

        $query = Invoice::query();


        $filter = $request->all();

        $query->with('contact');

        if (isset($filter['getInvoiceItem'])) {
            $query = $query->with('items');
        }

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

        if (isset($filter['user_id'])) {
            if (is_array($filter['user_id'])) {
                $query = $query->whereIn('user_id', $filter['user_id']);
            } else {
                $query = $query->where('user_id', $filter['user_id']);
            }
        }

        if (isset($filter['landing_page_id'])) {
            if (is_array($filter['landing_page_id'])) {
                $query = $query->whereIn('landing_page_id', $filter['landing_page_id']);
            } else {
                $query = $query->where('landing_page_id', $filter['landing_page_id']);
            }
        }

        if (isset($filter['contact_lead_process_id'])) {
            if (is_array($filter['contact_lead_process_id'])) {
                $query = $query->whereIn('contact_lead_process_id', $filter['contact_lead_process_id']);
            } else {
                $query = $query->where('contact_lead_process_id', $filter['contact_lead_process_id']);
            }
        }

        if (isset($filter['status'])) {
            if (is_array($filter['status'])) {
                $query = $query->whereIn('status', $filter['status']);
            } else {
                $query = $query->where('status', $filter['status']);
            }
        }

        if (isset($filter['phone'])) {
            $query = $query->whereHas('contact', function ($use) use ($filter) {
                if (is_array($filter['phone'])) {
                    $use->whereIn('phone', $filter['phone']);
                } else {
                    $use->where('phone', $filter['phone']);
                }
            });
        }
        if (isset($filter['merchant_code'])) {
            if (is_array($filter['merchant_code'])) {
                $query = $query->whereIn('merchant_code', $filter['merchant_code']);
            } else {
                $query = $query->where('merchant_code', $filter['merchant_code']);
            }
        }


        if (isset($filter['start_date'])) {
            $query = $query->where('created_at', '>',
                Carbon::createFromFormat('Y-m-d', $filter['start_date'])->startOfDay()->format('Y-m-d H:i:s'));
        }

        if (isset($filter['end_date'])) {
            $query = $query->where('created_at', '<=',
                Carbon::createFromFormat('Y-m-d', $filter['end_date'])->endOfDay()->format('Y-m-d H:i:s'));
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

        if (isset($filter['department_id'])) {
            $query = $query->whereHas('landingPage', function ($query) use ($filter) {
                if (is_array($filter['department_id'])) {
                    $query->whereIn('department_id', $filter['department_id']);
                } else {
                    $query->where('department_id', $filter['department_id']);
                }
            });
        }

        $invoices = $query->orderByDesc('id');
        if (isset($filter['get_all'])) {
            $invoices = $query->get();
        }else {
            if (isset($filter['export'])) {
                $invoices = $query->paginate($request->get('limit', 100));
            }else{
                $invoices = $query->paginate($request->get('limit', 20));
            }
        }
        return response()->json([
            'message' => 'get success',
            'data' => [
                'invoices' => $invoices->setCollection($invoices->getCollection()->makeVisible('created_at'))
            ]
        ]);
    }
    public function store(InvoiceRequest $request)
    {


        $data = $request->validated();

        $data['code'] = Str::uuid();

        if (!$this->checkPrice($data)) {
            return response()->json([
                'code' => 422,
                'message' => 'Update fail, money not true',
                'data' => [
                ]
            ], 422);
        }

        $contactProcess = ContactLeadProcess::where('user_id', $data['user_id'])
            ->where('landing_page_id', $data['landing_page_id'])->orderBy('id', 'desc')->first();
        $data['contact_lead_process_id'] = $contactProcess->id ?? 0;

        if (!$contactProcess) {
            return response()->json([
                'code' => 404,
                'message' => 'contact not found!',
            ], 404);
        }

        try {
            $invoice = DB::transaction(function () use ($data) {
                if (is_null($data['voucher_code'])) {
                    $data['voucher_code'] = '';
                }
                /***
                 * @var $invoice Invoice
                 */
                $invoice = Invoice::create($data);


                foreach ($data['item_product_id'] as $key => $eachItem) {
                    $invoice->items()->create([
                        'product_id' => $eachItem,
                        'product_type' => $data['item_product_type'][$key],
                        'product_name' => $data['item_product_name'][$key] ?? '',
                        'quantity' => $data['item_quantity'][$key],
                        'price' => $data['item_price'][$key],
                        'discount' => $data['item_discount'][$key],
                    ]);
                }
                $invoice->items;

                $invoice->makeVisible('code');

                return $invoice;
            });

            return response()->json([
                'code' => 200,
                'message' => 'create success',
                'data' => [
                    'invoice' => $invoice,
                ]
            ]);
        } catch (\Exception $exception) {
            throw $exception;
        }


    }

    public function update($id, InvoiceRequest $request)
    {
        $data = $request->validated();
        /***
         * @var $invoice Invoice
         */
        $invoice = Invoice::find($id);

        if (($data['code'] ?? '') != $invoice->code) {
            return response()->json([
                'code' => 422,
                'message' => 'code not true',
                'data' => [
                ]
            ], 422);
        }
        $contactProcess = ContactLeadProcess::where('user_id', $data['user_id'])
            ->where('landing_page_id', $data['landing_page_id'])->orderBy('id', 'desc')->first();
        $data['contact_lead_process_id'] = $contactProcess->id ?? 0;

        if (!$contactProcess) {
            return response()->json([
                'code' => 404,
                'message' => 'contact not found!',
            ], 404);
        }

        try {
            $invoice = DB::transaction(function () use ($invoice, $data) {

                $invoice->fill($data);
                $invoice->save();
                if ($data['item_product_id'] ?? null) {
                    $invoice->items()->delete();
                    foreach ($data['item_product_id'] as $key => $eachItem) {
                        $invoice->items()->create([
                            'product_id' => $eachItem,
                            'product_type' => $data['item_product_type'][$key],
                            'product_name' => $data['item_product_name'][$key] ?? '',
                            'quantity' => $data['item_quantity'][$key],
                            'price' => $data['item_price'][$key],
                            'discount' => $data['item_discount'][$key],
                        ]);
                    }
                }


                $invoice->items;

                return $invoice;
            });

            return response()->json([
                'code' => 200,
                'message' => 'update success',
                'data' => [
                    'invoice' => $invoice,
                ]
            ]);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function show($id)
    {
        $invoice = Invoice::with('items')->find($id);

        if ($invoice) {
            return response()->json([
                'code' => 200,
                'message' => 'get success',
                'data' => [
                    'invoice' => $invoice,
                ]
            ]);
        } else {
            return response()->json([
                'code' => 200,
                'message' => 'invoice not found',
                'data' => [
                    'invoice' => $invoice,
                ]
            ]);
        }


    }

    private function checkPrice($data)
    {
        $sumPrice = 0;

        foreach ($data['item_product_id'] as $key => $eachItem) {
            $sumPrice += ($data['item_price'][$key] - $data['item_discount'][$key]) * $data['item_quantity'][$key];
        }

        if ($sumPrice == $data['amount']) {
            return true;
        } else {
            return false;
        }
    }
}
