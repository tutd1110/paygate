<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationError extends Mailable
{
    use Queueable, SerializesModels;

    /***
     * @var $exception \Exception
     */
    private $exception;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($exception)
    {
        $this->exception = $exception;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->from(config('mail.from.address'), 'API Landing page');

        return $this->from(config('mail.from.address'))->view('mail.exception');
    }
}
