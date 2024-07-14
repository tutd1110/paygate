<?php

namespace App\Models\SYS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SysUserGroup extends Model
{
    use HasFactory;

    protected $table = 'sys_user_groups';

    protected $fillable = [
        'user_id',
        'group_id',
        'created_by',
        'updated_by'
    ];
    public function permissionGroup()
    {
        return $this->hasMany(SysGroupPermission::class, 'group_id','group_id');
    }
    public function permissionGroupRoute()
    {
        return $this->belongsToMany(SysPermission::class, 'sys_group_permissions', 'group_id', 'permission_id','group_id');
    }
}

