<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;


    protected $table = 'departments';

    protected $fillable
        = [
            'id',
            'code',
            'name',
            'description',
            'quota',
            'is_active',
            'is_delete',
            'branch_id',
            'branch_name',
            'created_by',
            'updated_by',
        ];

}
