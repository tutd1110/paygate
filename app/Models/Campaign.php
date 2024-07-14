<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $table = 'campaigns';

    protected $fillable
        = [
            'department_id',
            'code',
            'name',
            'description',
            'start_date',
            'adverting_budget',
            'amount_spent',
            'is_active',
            'is_delete',
            'created_by',
            'updated_by',
        ];
}
