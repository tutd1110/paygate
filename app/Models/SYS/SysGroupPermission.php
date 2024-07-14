<?php

namespace App\Models\SYS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SysGroupPermission extends Model
{
    use HasFactory;

    protected $table = 'sys_group_permissions';

    protected $fillable = [
        'group_id',
        'permission_id',
        'created_by',
        'updated_by'
    ];
    public function groupPermisson()
    {
        return $this->belongsTo(SysPermission::class, 'permission_id','id');
    }
}
