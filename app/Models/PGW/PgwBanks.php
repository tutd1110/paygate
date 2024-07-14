<?php

namespace App\Models\PGW;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PgwBanks extends Model
{
    use HasFactory;
    protected $table = 'pgw_banking_lists';

    protected $fillable = [
        'code',
        'name',
        'thumb_path',
        'status',
        'created_by',
        'updated_by',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    const TYPE_BILLING = 'billing';
    const TYPE_TOPUP = 'topup';
}
