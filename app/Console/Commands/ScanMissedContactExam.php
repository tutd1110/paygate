<?php

namespace App\Console\Commands;

use App\Models\ContactExam;
use App\Models\ContactExamLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScanMissedContactExam extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scan_missed_contact_exam';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ContactExam $contactExams, ContactExamLog $contactExamLogs)
    {
        $this->contactExams = $contactExams;
        $this->contactExamLogs = $contactExamLogs;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('[' . Carbon::now()->format('Y-m-d H:i:s') . '] Start Run: ' . self::class . '');
        $name = $this->ask('Please choose: scan_missed_exam/convert_exam');
        if ($name == 'scan_missed_exam' ){
            $this->scanMissedContactExam();
            $this->info('[' . Carbon::now()->format('Y-m-d H:i:s') . '] Run Success: ' . self::class);
        }elseif ($name == 'convert_exam'){
            $this->convertContactExam();
            $this->info('[' . Carbon::now()->format('Y-m-d H:i:s') . '] Run Success: ' . self::class);
        }else{
            $this->error( "Error!! Choose again!!");
            $this->info('[' . Carbon::now()->format('Y-m-d H:i:s') . '] Run Success: ' . self::class);
        }
        return true;
    }


    public function scanMissedContactExam()
    {
//
        /***
         * Xoá các bài thi không hợp lệ
         */

        $deleteContactExamInValid = $this->contactExams
            ->where('created_at', '<', Carbon::now()->subMinutes(15))
            ->doesntHave("contactExamLogs")->delete();

        $listContactExams = $this->contactExams::whereNull('updated_at')
            ->whereHas('contactExamLogs', function ($query) {
                $query->whereNotNull('updated_at');
            })->withSum('contactExamLogs', 'score')->withSum('contactExamLogs', 'time')->withCount('contactExamLogs')->get();

        $listUserExamInValidIds = $listContactExams->map(function ($exam) {
            return $exam->contact_lead_process_id;
        })->unique()->toArray();


        /***
         * Lấy ra các bài thi đã submit, tính tổng điểm, tổng thời gian
         */

        $contactExamValid = $this->contactExams->whereNotNull('updated_at')
            ->whereIn('contact_lead_process_id', $listUserExamInValidIds)->get();
        if (!empty($contactExamValid) && !empty($listContactExams)) {

            /***
             * Lấy ra giá trị tổng điểm lớn nhất trong bảng log
             */

            $listExamMaxPoint = collect([]);
            $listDeleteContactExams = [];

            foreach ($listContactExams as $exam) {

                if (!empty($listExamMaxPoint[$exam->contact_lead_process_id])) {
                    if ($listExamMaxPoint[$exam->contact_lead_process_id]->contact_exam_logs_sum_score < $exam->contact_exam_logs_sum_score) {
                        array_push($listDeleteContactExams, $listExamMaxPoint[$exam->contact_lead_process_id]->id);
                        $listExamMaxPoint[$exam->contact_lead_process_id] = $exam;
                        $listExamMaxPoint[$exam->contact_lead_process_id]->number = $listExamMaxPoint[$exam->contact_lead_process_id]->number + 1;

                    } elseif ($listExamMaxPoint[$exam->contact_lead_process_id]->contact_exam_logs_sum_score == $exam->contact_exam_logs_sum_score) {
                        if ($listExamMaxPoint[$exam->contact_lead_process_id]->contact_exam_logs_sum_time > $exam->contact_exam_logs_sum_time) {
                            array_push($listDeleteContactExams, $listExamMaxPoint[$exam->contact_lead_process_id]->id);
                            $listExamMaxPoint[$exam->contact_lead_process_id] = $exam;
                            $listExamMaxPoint[$exam->contact_lead_process_id]->number = $listExamMaxPoint[$exam->contact_lead_process_id]->number + 1;
                        } else {
                            array_push($listDeleteContactExams, $exam->id);
                            $listExamMaxPoint[$exam->contact_lead_process_id]->number = $listExamMaxPoint[$exam->contact_lead_process_id]->number + 1;
                        }
                    } else {
                        array_push($listDeleteContactExams, $exam->id);
                        $listExamMaxPoint[$exam->contact_lead_process_id]->number = $listExamMaxPoint[$exam->contact_lead_process_id]->number + 1;
                    }
                } else {
                    $listExamMaxPoint[$exam->contact_lead_process_id] = $exam;
                }

            }

            $listExamMaxPoint = $listExamMaxPoint->map(function ($item) {
                return $item;
            });

            /***
             * So sánh và lấy ra tổng điểm lớn nhất trong các kết quả của contact_exam_logs và contact_exam.
             */
            foreach ($contactExamValid as $contactExams) {

                if (!empty($listExamMaxPoint[$contactExams->contact_lead_process_id])) {
                    if ($listExamMaxPoint[$contactExams->contact_lead_process_id]->contact_exam_logs_sum_score < $contactExams->total_score) {
                        array_push($listDeleteContactExams, $listExamMaxPoint[$contactExams->contact_lead_process_id]->id);
                        $contactExams->number = $contactExams->number + $listExamMaxPoint[$contactExams->contact_lead_process_id]->number;
                        $listExamMaxPoint[$contactExams->contact_lead_process_id] = $contactExams;

                    } elseif ($listExamMaxPoint[$contactExams->contact_lead_process_id]->contact_exam_logs_sum_score == $contactExams->total_score) {
                        if ($listExamMaxPoint[$contactExams->contact_lead_process_id]->contact_exam_logs_sum_time > $contactExams->total_time) {
                            array_push($listDeleteContactExams, $listExamMaxPoint[$contactExams->contact_lead_process_id]->id);
                            $contactExams->number = $contactExams->number + $listExamMaxPoint[$contactExams->contact_lead_process_id]->number;
                            $listExamMaxPoint[$contactExams->contact_lead_process_id] = $contactExams;
                        } else {
                            array_push($listDeleteContactExams, $contactExams->id);
                            $listExamMaxPoint[$contactExams->contact_lead_process_id]->number = $contactExams->number + $listExamMaxPoint[$contactExams->contact_lead_process_id]->number;
                        }
                    } else {
                        array_push($listDeleteContactExams, $contactExams->id);
                        $listExamMaxPoint[$contactExams->contact_lead_process_id]->number = $contactExams->number + $listExamMaxPoint[$contactExams->contact_lead_process_id]->number;
                    }
                } else {
                    $listExamMaxPoint[$contactExams->contact_lead_process_id] = [];
                }
            }

            $contactExamDelelte = $this->contactExams
                ->whereIn('id', $listDeleteContactExams)->delete();

            /***
             * Lưu kết quả vào trong database.
             */
            $data = [];
            foreach ($listExamMaxPoint as $examMax) {
                $contactLeadProcessId = $examMax['contact_lead_process_id'];
                $data['total_score'] = isset($examMax['contact_exam_logs_sum_score']) ? $examMax['contact_exam_logs_sum_score'] : $examMax['total_score'];
                $data['total_time'] = isset($examMax['contact_exam_logs_sum_time']) ? $examMax['contact_exam_logs_sum_time'] : $examMax['total_time'];
                $data['session_id'] = $examMax['session_id'];;
                $data['total_question'] = isset($examMax['contact_exam_logs_count']) ? $examMax['contact_exam_logs_count'] : $examMax['total_question'];
                $data['is_done'] = $examMax['total_question'] == 30 ? true : false;
                $data['created_at'] = $examMax['created_at'];
//                $data['number'] = $examMax['number'];
                $data['number'] = count($this->contactExamLogs->where('contact_lead_process_id',$contactLeadProcessId)->groupBy('session_id')->get());
                $data['updated_at'] = isset($examMax['updated_at']) ? $examMax['updated_at'] : Carbon::now();
                $this->contactExams->where('contact_lead_process_id', $contactLeadProcessId)->update($data);
            }
        } else {
            $listExamMaxPoint = collect([]);
            $listDeleteContactExams = [];

            if ($listContactExams) {
                foreach ($listContactExams as $exam) {
                    if (!empty($listExamMaxPoint[$exam->contact_lead_process_id])) {
                        if ($listExamMaxPoint[$exam->contact_lead_process_id]->contact_exam_logs_sum_score < $exam->contact_exam_logs_sum_score) {
                            array_push($listDeleteContactExams, $listExamMaxPoint[$exam->contact_lead_process_id]->id);
                            $listExamMaxPoint[$exam->contact_lead_process_id] = $exam;
                            $listExamMaxPoint[$exam->contact_lead_process_id]->number++;
                        } elseif ($listExamMaxPoint[$exam->contact_lead_process_id]->contact_exam_logs_sum_score == $exam->contact_exam_logs_sum_score) {
                            if ($listExamMaxPoint[$exam->contact_lead_process_id]->contact_exam_logs_sum_time > $exam->contact_exam_logs_sum_time) {
                                array_push($listDeleteContactExams, $listExamMaxPoint[$exam->contact_lead_process_id]->id);
                                $listExamMaxPoint[$exam->contact_lead_process_id] = $exam;
                                $listExamMaxPoint[$exam->contact_lead_process_id]->number++;
                            } else {
                                array_push($listDeleteContactExams, $exam->id);
                                $listExamMaxPoint[$exam->contact_lead_process_id]->number++;
                            }
                        } else {
                            array_push($listDeleteContactExams, $exam->id);
                            $listExamMaxPoint[$exam->contact_lead_process_id]->number++;
                        }
                    } else {
                        $listExamMaxPoint[$exam->contact_lead_process_id] = $exam;
                    }
                }
                $contactExamDelelte = $this->contactExams
                    ->whereIn('id', $listDeleteContactExams)->delete();
                $data = [];
                foreach ($listContactExams as $examMax) {
                    $contactLeadProcessId = $examMax['contact_lead_process_id'];
                    $data['total_score'] = isset($examMax["contact_exam_logs_sum_score"]) ? $examMax["contact_exam_logs_sum_score"] : $examMax['total_score'];
                    $data['total_time'] = isset($examMax["contact_exam_logs_sum_time"]) ? $examMax["contact_exam_logs_sum_time"] : $examMax['total_time'];
                    $data['contact_lead_process_id'] = $contactLeadProcessId;
                    $data['session_id'] = $examMax['session_id'];;
                    $data['total_question'] = isset($examMax["contact_exam_logs_count"]) ? $examMax["contact_exam_logs_count"] : $examMax['total_question'];
                    $data['is_done'] = $examMax['total_question'] == 30 ? true : false;
                    $data['created_at'] = $examMax['created_at'];
                    $data['number'] = $examMax['number'];
                    $data['updated_at'] = isset($examMax['updated_at']) ? $examMax['updated_at'] : Carbon::now();
                    $this->contactExams->where('contact_lead_process_id', $contactLeadProcessId)->update($data);
                }
            }
        }
    }
    public function convertContactExam(){
        $deleteContactExamInValid = $this->contactExams
            ->where('created_at', '<', Carbon::now()->subMinutes(15))
            ->doesntHave("contactExamLogs")->delete();
        $arrayContactLeadProcessID = $this->contactExams->whereNotNull('updated_at')
            ->get('contact_lead_process_id')->toArray();

        foreach ($arrayContactLeadProcessID as $contactLeadProcessID) {
            $id = $contactLeadProcessID['contact_lead_process_id'];
            $data['number'] = count($this->contactExamLogs->where('contact_lead_process_id', $id)->groupBy('session_id')->get());
            $this->contactExams->where('contact_lead_process_id', $id)->whereNotNull('updated_at')->update(['number'=>$data['number']]);
        }
    }
}

