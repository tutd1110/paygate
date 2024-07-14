<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActiveCode extends Model
{
    const USED_YES = 'yes';
    const USED_NO = 'no';
    const PRODUCT_ID_DEFAULT = '0';
    const COUNT_WARNING_ACTIVE_CODE_START = 1;
    const COUNT_WARNING_ACTIVE_CODE_FINISH = 30;
    const COUNT_WARNING_ACTIVE_CODE_DIVIDE = 5;
    const EXPIRED_REDIS_ACTIVE_CODE = 300;
    use HasFactory;

    protected $table = 'active_code';

    protected $fillable = [
        'id',
        'landing_page_id',
        'code',
        'used',
        'created_at',
        'updated_at',
    ];
}
