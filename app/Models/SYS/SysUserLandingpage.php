<?php

namespace App\Models\SYS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SysUserLandingpage extends Model
{
    use HasFactory;
    protected $table = 'sys_user_landing_page';

    protected $fillable = [
        'user_id',
        'landing_page_id',
        'created_by',
        'updated_by'
    ];
}
