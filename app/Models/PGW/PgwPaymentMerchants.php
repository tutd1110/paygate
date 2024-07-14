<?php

namespace App\Models\PGW;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PgwPaymentMerchants extends Model
{
    const MERCHANT_TRANSFER = 'transfer';
    use HasFactory;
    protected $table = 'pgw_payment_merchants';

    protected $fillable = [
        'code',
        'name',
        'thumb_path',
        'status',
        'sort',
        'description',
        'created_by',
        'updated_by'
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
}
