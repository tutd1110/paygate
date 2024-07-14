<?php

namespace App\Models\SYS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SysPermission extends Model
{
    use HasFactory;

    protected $table = 'sys_permissions';

    protected $fillable = [
        'module_id',
        'name',
        'name_alias',
        'router',
        'created_by',
        'updated_by'
    ];
    public function module()
    {
        return $this->belongsTo(SysModule::class, 'module_id', 'id', );
    }
}
