<?php

namespace App\Jobs;

use App\Helper\Request;
use App\Models\EmailSave;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEmailHocMai implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /****
     * @var $emailSave EmailSave
     */
    private $emailSave;
    private $token;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(EmailSave $emailSave)
    {
        $this->token = config('hocmai.TOKEN_SEND_MAIL');
        $this->emailSave = $emailSave;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /***
         * gửi email tới khách hàng
         */
        try {
            $emailSend = Request::post(config('hocmai.hocmai_url').'/api/email/send', [
                'headers' => [
                    'TOKEN' => $this->token,
                ],
                'form_params' => [
                    'email' => $this->emailSave->to_email,
                    'subject' => $this->emailSave->subject,
                    'full_name' => $this->emailSave->to_name,
                    'content' => htmlspecialchars($this->emailSave->content),
                    'cc' => null,
                    'bcc' => null,
                ]
            ]);
            $emailSendData = json_decode($emailSend->getBody());

            if ($emailSendData->status == 'success' && $emailSendData->code == 200) {
                $this->emailSave->send_error = 0;
                $this->emailSave->status = 'sent';

                return true;
            } else {
                $this->emailSave->send_error = 1;
                throw new \Exception('Gửi mail không thành công: '.(string)$emailSendData);
            }
        } catch (\Exception $exception) {
            $this->emailSave->send_error = 1;

            throw  $exception;
        } finally {
            $this->emailSave->save();
        }


    }
}
