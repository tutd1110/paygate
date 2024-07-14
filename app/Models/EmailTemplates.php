<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplates extends Model
{
    use HasFactory;

    protected $table = 'email_templates';

    protected $fillable
        = [
            'id',
            'code',
            'name',
            'subject',
            'content',
            'status',
            'attachment_files',
            'description',
            'created_at',
            'updated_at',
            'created_by',
            'updated_by',
        ];

}
