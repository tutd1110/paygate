<?php

namespace App\Models\SYS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SysModule extends Model
{
    use HasFactory;

    protected $table = 'sys_modules';

    protected $fillable = [
        'module',
        'module_alias',
        'created_by',
        'updated_by'
    ];
    public function sysPermissions()
    {
        return $this->hasMany(SysPermission::class, 'module_id','id');
    }
}
