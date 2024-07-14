<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogClientRequest extends Model
{
    use HasFactory;

    protected $table = 'log_client_requests';

    protected $fillable  = [
        'url',
        'ip',
        'status_code',
        'data',
        'method',
        'header',
        'response'
    ];
}
