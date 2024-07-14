<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailSave\EmailSaveRequest;
use App\Jobs\SendEmailHocMai;
use App\Models\EmailSave;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmailSaveController extends Controller
{

    /***
     * @var EmailSave
     */
    private $mail;

    public function __construct(EmailSave $emailSave)
    {
        $this->mail = $emailSave;
    }

    public function index(Request $request)
    {
        $query = $this->mail::query();

        $query->with('contact');
        $query->with('landingPage');

        $emailSaves = $query
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('limit', 20));

        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'mails' => $emailSaves,
            ]
        ]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response | mixed
     */
    public function store(EmailSaveRequest $request)
    {
        $data = $request->validated();
        $data['status'] = 'waiting';

        $email = $this->mail->create($data);


        if (Carbon::now()->timestamp > Carbon::parse($email->send_time)->timestamp) {
            SendEmailHocMai::dispatch($email);
        } else {
            SendEmailHocMai::dispatch($email)->delay(Carbon::parse($email->send_time));
        }


        return response()->json([
            'status' => 200,
            'message' => 'success email will send now',
            'data' => [
                'mail' => $email
            ]
        ]);
    }

}
