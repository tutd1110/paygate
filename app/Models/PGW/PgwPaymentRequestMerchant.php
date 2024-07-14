<?php

namespace App\Models\PGW;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PgwPaymentRequestMerchant extends Model
{
    use HasFactory,Compoships;
    protected $table = 'pgw_payment_request_merchants';

    const PAID_STATUS_SUCCESS = 'success';
    const PAID_STATUS_UNSUCCESS = 'unsuccess';


    const TRANSACTION_STATUS_YES = 'Y';
    const TRANSACTION_STATUS_NO = 'N';

    const TRIES_GET_NUMBER_RANDOM = 3;
    const LENGTH_NUMBER_RANDOM = 9;
}
