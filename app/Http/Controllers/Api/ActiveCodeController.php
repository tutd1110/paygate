<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActiveCode\ActiveCodeRequest;
use App\Models\ActiveCode;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ActiveCodeController extends Controller
{
    public function __construct()
    {

        $this->activeCode = app()->make(ActiveCode::class);
    }

    public function index(Request $request)
    {
        $filter = $request->all();
        $query = $this->activeCode::query();
        if (isset($filter['id'])) {
            if (is_array($filter['id'])) {
                $query = $query->whereIn('id', $filter['id']);
            } else {
                $query = $query->where('id', $filter['id']);
            }
        }
        if (isset($filter['landing_page_id'])) {
            if (is_array($filter['landing_page_id'])) {
                $query = $query->whereIn('landing_page_id', $filter['landing_page_id']);
            } else {
                $query = $query->where('landing_page_id', $filter['landing_page_id']);
            }
        }
        if (isset($filter['start_date'])) {
            $query = $query->where('created_at', '>=', Carbon::createFromFormat('Y-m-d', $filter['start_date'])->startOfDay()->format('Y-m-d H:i:s'));
        }
        if (isset($filter['end_date'])) {
            $query = $query->where('created_at', '<=', Carbon::createFromFormat('Y-m-d', $filter['end_date'])->endOfDay()->format('Y-m-d H:i:s'));
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
        $activeCode = $query->paginate($request->get('limit', config('cms.limit')));
        return response()->json([
            'message' => 'success',
            'data' => [
                'active_code' => $activeCode
            ]
        ]);

    }
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(ActiveCodeRequest $request)
    {
        try {
            $data = $request->validated();
            ActiveCode::insert($data['active_code']);
            return response()->json([
                'data' => [
                    'status' => true,
                    'message' => 'success',
                    'active_code' => $data['active_code'],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 400,
                'message' => 'Bad request, somethings went wrong',
                'error' => $e->getMessage()
            ]);
        }

    }

}
