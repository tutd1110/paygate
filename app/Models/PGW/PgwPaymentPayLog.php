<?php

namespace App\Models\PGW;

use Illuminate\Database\Eloquent\Model;

class PgwPaymentPayLog extends Model
{
    protected $table = 'pgw_payment_pay_logs';

    const PAID_STATUS_SUCCESS = 'success';
    const PAID_STATUS_UNSUCCESS = 'unsuccess';
}
