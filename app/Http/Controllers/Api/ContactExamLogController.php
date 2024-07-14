<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactExamLog\ContactExamLogRequest;
use App\Http\Requests\ContactExamLog\ListContactExamLog;
use App\Models\ContactExamLog;
use App\Models\LandingPageInfo;
use Illuminate\Http\Request;

class ContactExamLogController extends Controller
{
    /***
     * @var ContactExamLog
     */
    private $contactExamLogs;

    public function __construct(ContactExamLog $contactExamLogs){
        $this->contactExamLogs = $contactExamLogs;
    }
    public function index(ListContactExamLog $request)
    {
        $query = $this->contactExamLogs;
        $filter = $request->all();
        if (isset($filter['contact_lead_process_id'])) {
            if (is_array($filter['contact_lead_process_id'])) {
                $query = $query->whereIn('contact_lead_process_id', $filter['contact_lead_process_id']);
            } else {
                $query = $query->where('contact_lead_process_id', $filter['contact_lead_process_id']);
            }
        }
        if (isset($filter['session_id'])) {
            if (is_array($filter['session_id'])) {
                $query = $query->whereIn('session_id', $filter['session_id']);
            } else {
                $query = $query->where('session_id', $filter['session_id']);
            }
        }
        if ($request->get('count')) {
            $contactExamLogs = $query->paginate($request->get('limit', 20));
        } else {
            $contactExamLogs = $query->simplePaginate($request->get('limit', 20));
        }
        return response()->json([
            'status'=>true,
            'message' => 'success',
            'data' => [
                'contactExamLogs' => $contactExamLogs
            ]
        ]);
    }

    public function store(ContactExamLogRequest $request)
    {
        $contactExamLogs = $this->contactExamLogs->create($request->all());
        return response()->json([
            'status'=>true,
            'message' => 'success',
            'data' => [
                'contactExamLogs' => $contactExamLogs
            ]
        ]);
    }
}
