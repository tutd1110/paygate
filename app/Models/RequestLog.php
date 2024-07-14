<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    use HasFactory;

    protected $table = 'request_logs';

    protected $fillable = [
        'url',
        'option',
        'status_code',
        'headers',
        'method',
        'response',
        'is_success',
        'exception_info',
        'file',
    ];
}
