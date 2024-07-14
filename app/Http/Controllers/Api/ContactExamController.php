<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactExam\ContactExamRequest;
use App\Models\ContactExam;
use App\Models\ContactExamLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ContactLeadProcess;

class ContactExamController extends Controller
{
    /***
     * @var ContactExam
     */
    private $contactExams;
    /***
     * @var ContactExamLog
     */
    private $contactExamLogs;

    public function __construct(ContactExam $contactExams, ContactExamLog $contactExamLogs, ContactLeadProcess $contactLeadProcess)
    {
        $this->contactExams = $contactExams;
        $this->contactExamLogs = $contactExamLogs;
        $this->contactLeadProcess = $contactLeadProcess;
    }

    public function index(Request $request)
    {
        $filter = $request->all();
        $query = $this->contactExams::query()->whereNotNull('updated_at');
        $query->with('contact');
        if (isset($filter['fullname'])) {
            $query = $query->whereHas('contact', function ($query) use ($filter) {
                if (is_array($filter['fullname'])) {
                    $query->whereIn('full_name', 'like', '%'.$filter['fullname'] . '%');
                } else {
                    $query->where('full_name', 'like', '%'.$filter['fullname'] . '%');
                }
            });
        }
        if (isset($filter['phone'])) {
            $query = $query->whereHas('contact', function ($query) use ($filter) {
                if (is_array($filter['phone'])) {
                    $query->whereIn('phone', $filter['phone']);
                } else {
                    $query->where('phone', $filter['phone']);
                }
            });
        }
        if (isset($filter['email'])) {
            $query = $query->whereHas('contact', function ($query) use ($filter) {
                if (is_array($filter['email'])) {
                    $query->whereIn('email', $filter['email']);
                } else {
                    $query->where('email', $filter['email']);
                }
            });
        }
        if (isset($filter['order'])) {
            if (is_array($filter['order'])) {
                foreach ($filter['order'] as $key => $value) {
                    $query = $query->orderBy($value, $filter['direction'][$key] ?? 'asc')->distinct();;
                }
            } else {
                $filter['order'] = explode(',', $filter['order']);
                $filter['direction'] = explode(',', $filter['direction']);

                foreach ($filter['order'] as $key => $value) {
                    $query = $query->orderBy($value, $filter['direction'][$key] ?? 'asc')->distinct();;
                }
            }
        }
         if ($request->get('export') == true) {
             $contactExams['data'] = $query->distinct()->get() ;
         }elseif (isset($filter['limit'])) {
            $contactExams = $query
                ->paginate($request->get('limit', 20));
        } else {
            $contactExams = $query
                ->orderBy('id', 'desc')
                ->paginate($request->get('limit', 20));
        }


        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'contactExams' => $contactExams,
            ]
        ]);
    }


    public function store(ContactExamRequest $request)
    {
        $filter = $request->validated();

        if ($filter) {
            $contactLeadProcessId = $request->contact_lead_process_id;
            $sessionID = $request->session_id;

            /***
             * Check xem thí sinh làm bài thi nào chưa để xử lí tạo mới và cập nhật
             */

            $getContactExam = $this->contactExams
                ->where('contact_lead_process_id', $contactLeadProcessId)
                ->where('session_id', $sessionID)
                ->first();
            if (!$getContactExam) {
                $filter['number'] = 0;
                $filter['updated_at'] = null;
                $contactExams = $this->contactExams->create($filter);
            } else {
                $contactExams = $this->update($filter);
            }

            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => [
                    'contactExams' => $contactExams
                ]
            ]);
        }
    }

    public function show($id)
    {
        $contactExams = $this->contactExams->find($id);

        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'contactExams' => $contactExams
            ]
        ]);
    }

    public function update($data)
    {
        $contactLeadProcessId = $data['contact_lead_process_id'];
        $sessionID = $data['session_id'];
        $data['updated_at'] = Carbon::now();
        $getContactExam = $this->contactExams
            ->where('contact_lead_process_id', $contactLeadProcessId)
            ->whereNotNull('updated_at')
            ->first();
        if (!$getContactExam){
            $getContactExam = $this->contactExams
                ->where('contact_lead_process_id', $contactLeadProcessId)
                ->where('session_id', $sessionID)
                ->first();
        }
        $data['created_at'] = $getContactExam['created_at'];
        $data['number'] = $getContactExam['number'] + 1;
        $getContactExam['number'] = $getContactExam['number'] + 1;

        /***
         * Kiểm tra bài thi nào có số điểm cao hơn thì lưu lại
         */

        if ($getContactExam['total_score'] < $data['total_score']) {
            $getContactExam->delete();

            $this->contactExams
                ->where('contact_lead_process_id', $contactLeadProcessId)
                ->where('session_id', $sessionID)
                ->delete();
            $this->contactExams->create($data);
            $contactExams = $data;
        } elseif ($getContactExam['total_score'] == $data['total_score']) {
            /***
             * Nếu số điểm bằng nhau thì lưu bài thi có thời gian ngắn hơn
             */

            if ($getContactExam['total_time'] >= $data['total_time']) {
                $getContactExam->delete();
                $this->contactExams
                    ->where('contact_lead_process_id', $contactLeadProcessId)
                    ->where('session_id', $sessionID)
                    ->delete();
                $this->contactExams->create($data);
                $contactExams = $data;
            } else {
                $getContactExam->update();
                $this->contactExams
                    ->where('contact_lead_process_id', $contactLeadProcessId)
                    ->where('session_id', $sessionID)
                    ->delete();
                $contactExams = $data;
            }

        } else {
            $getContactExam->update();
            $this->contactExams
                ->where('contact_lead_process_id', $contactLeadProcessId)
                ->where('session_id', $sessionID)
                ->delete();
            $contactExams = $data;
        }
        return $contactExams;
    }

    public function detailContactExams(Request $request)
    {

        $contactLeadProcessId = $request->contact_lead_process_id;
        $query = $this->contactExamLogs::query();
        $query = $query
            ->where('contact_lead_process_id',$contactLeadProcessId)
            ->select([
                'created_at',
                DB::raw('MAX(updated_at) as updated_at'),
                DB::raw('SUM(score) as total_score'),
                DB::raw('SUM(time) as total_time'),
                DB::raw('COUNT(question_id) as total_question')
            ])
            ->orderBy('updated_at','asc')
            ->groupBy('session_id');
        $contactExamLogs = $query->get();
        $contact = $this->contactLeadProcess->where('id',$contactLeadProcessId)->first();

        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'contactExamLogs' => $contactExamLogs,
                'contact' => $contact
            ]
        ]);
    }

}
