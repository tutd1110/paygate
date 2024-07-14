<?php

namespace App\Http\Controllers\Api\PGW;

use App\Exceptions\InvalidArgumentException;
use App\Exceptions\ResourceNotFoundException;
use App\Helper\Utm;
use App\Http\Controllers\Controller;
use App\Http\Requests\PGW\PgwStatisticalRequest ;
use App\Models\ContactLead;
use App\Models\ContactLeadProcess;
use App\Models\PGW\PgwOrder;
use App\Models\PGW\PgwPartnerRegistriBanking;
use App\Models\PGW\PgwPartnerResgistriMerchant;
use App\Models\PGW\PgwPaymentRequest;
use App\Models\Traffic;
use App\Payment\PayGate;
use App\Repositories\Contact\ContactEloquentRepository;
use App\Repositories\Contact\ContactPushEloquentRepository;
use App\Repositories\Contact\ContactPushRepositoryInterface;
use App\Repositories\Contact\ContactRepositoryInterface;
use App\Repositories\PGW\PgwOrderRepository;
use App\Repositories\PGW\PgwOrderInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class PgwStatisticalController extends Controller
{

    public function __construct(PgwOrderInterface $pgwOrderRes, ContactRepositoryInterface $contactEloquentRepository)
    {

    }

    public function statistical(PgwStatisticalRequest $request)
    {

        $filter = $request->validated();
        $selectedYear = $filter['year'] ?? Carbon::now()->year;
        $selectedMonth = $filter['month'] ?? Carbon::now()->month;
        $query = new PgwOrder();
        $count = $query::selectRaw('count(id) as count, month(created_at) as month')
            ->whereYear('created_at', $selectedYear)
            ->groupBy('month')
            ->pluck('count', 'month');
        $countPaid = $query::selectRaw('count(id) as count, month(created_at) as month')
            ->where('status', 'paid')
            ->whereYear('created_at', $selectedYear)
            ->groupBy('month')
            ->pluck('count', 'month');

        /** lấy thống kê đơn hàng theo tháng */
        if (isset($filter['selectMonth'])) {
            $count = $query::selectRaw('count(id) as count, day(created_at) as day')
                ->whereYear('created_at', $selectedYear)
                ->whereMonth('created_at', $selectedMonth)
                ->groupBy('day')
                ->pluck('count', 'day');
            $countPaid = $query::selectRaw('count(id) as count, day(created_at) as day')
                ->where('status', 'paid')
                ->whereYear('created_at', $selectedYear)
                ->whereMonth('created_at', $selectedMonth)
                ->groupBy('day')
                ->pluck('count', 'day');
            $count = $this->validHashDataStatistical($count, $selectedMonth, $selectedYear, 'month');
            $countPaid = $this->validHashDataStatistical($countPaid, $selectedMonth, $selectedYear, 'month');
        } else {
            $count = $this->validHashDataStatistical($count, $selectedMonth, $selectedYear, 'year');
            $countPaid = $this->validHashDataStatistical($countPaid, $selectedMonth, $selectedYear, 'year');
        }
        return response()->json([
            'message' => 'success',
            'data' => [
                'totalOrder' => $count,
                'totalOrderPaid' => $countPaid,
            ]
        ]);
    }



    public function statisticalMerchant(PgwStatisticalRequest $request)
    {
        $filter = $request->validated();
        $selectedYear = $filter['year'] ?? Carbon::now()->year;
        $selectedMonth = $filter['month'] ?? Carbon::now()->month;
        $query = new PgwOrder();
        $countMerchant = $query::selectRaw('count(id) as total_statistical, merchant_code,banking_code')
            ->whereYear('created_at', $selectedYear)
            ->groupBy('merchant_code', 'banking_code')
            ->get()
            ->toArray();
        $countMerchantPaid = $query::selectRaw('count(id) as total_statistical, merchant_code,banking_code')
            ->whereYear('created_at', $selectedYear)
            ->where('status', 'paid')
            ->groupBy('merchant_code', 'banking_code')
            ->get()
            ->toArray();

        if (isset($filter['selectMonth'])) {
            $countMerchant = $query::selectRaw('count(id) as total_statistical, merchant_code,banking_code')
                ->whereYear('created_at', $selectedYear)
                ->whereMonth('created_at', $selectedMonth)
                ->groupBy('merchant_code', 'banking_code')
                ->get()
                ->toArray();
            $countMerchantPaid = $query::selectRaw('count(id) as total_statistical, merchant_code,banking_code')
                ->whereYear('created_at', $selectedYear)
                ->whereMonth('created_at', $selectedMonth)
                ->where('status', 'paid')
                ->groupBy('merchant_code', 'banking_code')
                ->get()
                ->toArray();
            $countMerchant = $this->validHashDataStatisticalMerchant($countMerchant);
            $countMerchantPaid = $this->validHashDataStatisticalMerchant($countMerchantPaid);
        } else {
            $countMerchant = $this->validHashDataStatisticalMerchant($countMerchant);
            $countMerchantPaid = $this->validHashDataStatisticalMerchant($countMerchantPaid);
        }
        return response()->json([
            'message' => 'success',
            'data' => [
                'totalMerchant' => $countMerchant,
                'totalMerchantPaid' => $countMerchantPaid,
            ]
        ]);
    }

    public function statisticalRevenue(PgwStatisticalRequest $request)
    {
        $filter = $request->validated();
        $selectedYear = $filter['year'] ?? Carbon::now()->year;
        $selectedMonth = $filter['month'] ?? Carbon::now()->month;
        $query = new PgwOrder();
        $count = $query::selectRaw('sum(amount) as amount, month(created_at) as month')
            ->whereYear('created_at', $selectedYear)
            ->groupBy('month')
            ->pluck('amount', 'month');
        $countPaid = $query::selectRaw('sum(amount) as amount, month(created_at) as month')
            ->where('status', 'paid')
            ->whereYear('created_at', $selectedYear)
            ->groupBy('month')
            ->pluck('amount', 'month');

        /** lấy thống kê đơn hàng theo tháng */
        if (isset($filter['selectMonth'])) {
            $count = $query::selectRaw('sum(amount) as amount, day(created_at) as day')
                ->whereYear('created_at', $selectedYear)
                ->whereMonth('created_at', $selectedMonth)
                ->groupBy('day')
                ->pluck('amount', 'day');
            $countPaid = $query::selectRaw('sum(amount) as amount, day(created_at) as day')
                ->where('status', 'paid')
                ->whereYear('created_at', $selectedYear)
                ->whereMonth('created_at', $selectedMonth)
                ->groupBy('day')
                ->pluck('amount', 'day');
            $count = $this->validHashDataStatistical($count, $selectedMonth, $selectedYear, 'month');
            $countPaid = $this->validHashDataStatistical($countPaid, $selectedMonth, $selectedYear, 'month');
        } else {
            $count = $this->validHashDataStatistical($count, $selectedMonth, $selectedYear, 'year');
            $countPaid = $this->validHashDataStatistical($countPaid, $selectedMonth, $selectedYear, 'year');
        }
        return response()->json([
            'message' => 'success',
            'data' => [
                'totalRevenue' => $count,
                'totalRevenuePaid' => $countPaid,
            ]
        ]);
    }
    public function statisticalMerchantRevenue(PgwStatisticalRequest $request)
    {
        $filter = $request->validated();
        $selectedYear = $filter['year'] ?? Carbon::now()->year;
        $selectedMonth = $filter['month'] ?? Carbon::now()->month;
        $query = new PgwOrder();
        $countMerchant = $query::selectRaw('sum(amount) as total_statistical, merchant_code,banking_code')
            ->whereYear('created_at', $selectedYear)
            ->groupBy('merchant_code', 'banking_code')
            ->get()
            ->toArray();
        $countMerchantPaid = $query::selectRaw('sum(amount) as total_statistical, merchant_code,banking_code')
            ->whereYear('created_at', $selectedYear)
            ->where('status', 'paid')
            ->groupBy('merchant_code', 'banking_code')
            ->get()
            ->toArray();

        if (isset($filter['selectMonth'])) {
            $countMerchant = $query::selectRaw('sum(amount) as total_statistical, merchant_code,banking_code')
                ->whereYear('created_at', $selectedYear)
                ->whereMonth('created_at', $selectedMonth)
                ->groupBy('merchant_code', 'banking_code')
                ->get()
                ->toArray();
            $countMerchantPaid = $query::selectRaw('sum(amount) as total_statistical, merchant_code,banking_code')
                ->whereYear('created_at', $selectedYear)
                ->whereMonth('created_at', $selectedMonth)
                ->where('status', 'paid')
                ->groupBy('merchant_code', 'banking_code')
                ->get()
                ->toArray();
            $countMerchant = $this->validHashDataStatisticalMerchant($countMerchant);
            $countMerchantPaid = $this->validHashDataStatisticalMerchant($countMerchantPaid);
        } else {
            $countMerchant = $this->validHashDataStatisticalMerchant($countMerchant);
            $countMerchantPaid = $this->validHashDataStatisticalMerchant($countMerchantPaid);
        }
        return response()->json([
            'message' => 'success',
            'data' => [
                'totalMerchantRevenue' => $countMerchant,
                'totalMerchantRevenuePaid' => $countMerchantPaid,
            ]
        ]);
    }


    function validHashDataStatistical($data, $month, $year, $getMonthOrYear): array
    {
        if ($getMonthOrYear == 'month') {
            $validData = [
                '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20',
                '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31'
            ];
            $month30 = [4, 6, 9, 11];
            $hashData = [];
            foreach ($validData as $key) {
                if (isset($data[$key])) {
                    $hashData[$key] = $data[$key];
                } else {
                    $hashData[$key] = 0;
                }
            }
            if ($month == 2) {
                if ($year % 4 == 0 && $year % 100 != 0 || $year % 400 == 0) {
                    $arrUnset = [30, 31];
                } else {
                    $arrUnset = [29, 30, 31];
                }
                foreach ($arrUnset as $key => $value) {
                    unset($hashData[$value]);
                }
            } elseif (in_array($month, $month30)) {
                unset($hashData['31']);
            }
        } elseif ($getMonthOrYear == 'year') {
            $validData = [
                '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12',
            ];
            $hashData = [];
            foreach ($validData as $key) {
                if (isset($data[$key])) {
                    $hashData[$key] = $data[$key];
                } else {
                    $hashData[$key] = 0;
                }
            }
        }
        return $hashData;
    }

    function validHashDataStatisticalMerchant($data): array
    {
        $arrMerchant = [];
        $arrMerchant['no_merchant'] = 0;
        foreach ($data as $key => $value) {
            if (!empty($value['merchant_code'])) {
                if ($value['merchant_code'] == 'transfer') {
                    if (!empty($value['banking_code'])) {
                        $arrMerchant[strtoupper($value['banking_code'])] = $value['total_statistical'];
                    }else{
                        $arrMerchant['no_merchant'] += $value['total_statistical'];
                    }
                } else {
                    if (!empty($arrMerchant[strtoupper($value['merchant_code'])])) {
                        $arrMerchant[strtoupper($value['merchant_code'])] += $value['total_statistical'];
                    } else {
                        $arrMerchant[strtoupper($value['merchant_code'])] = $value['total_statistical'];
                    }
                }
            } else {
                $arrMerchant['no_merchant'] += $value['total_statistical'];
            }
        }
        //Gộp thống kê của cổng momo
        if (!empty($arrMerchant['MOMO']) && !empty($arrMerchant['MOMO_V3'])) {
            $arrMerchant['MOMO'] += $arrMerchant['MOMO_V3'];
            unset($arrMerchant['MOMO_V3']);
        }
        ksort($arrMerchant);
        return $arrMerchant;

    }
}
