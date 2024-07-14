<?php

namespace App\Models\PGW;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PgwBankingList extends Model
{
    use HasFactory;

    protected $table = 'pgw_banking_lists';
    protected $fillable = [
        'id',
        'code',
        'name',
        'thumb_path',
        'status',
        'created_by',
        'updated_by',
    ];
}
