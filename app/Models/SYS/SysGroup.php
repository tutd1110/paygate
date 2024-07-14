<?php

namespace App\Models\SYS;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SysGroup extends Model
{
    use HasFactory;

    protected $table = 'sys_groups';

    protected $fillable = [
        'partner_code',
        'name',
        'description',
        'status',
        'created_by',
        'updated_by'
    ];
    public function sysGroupPermission()
    {
        return $this->hasMany(SysGroupPermission::class, 'group_id','id');
    }
    public function groups()
    {
        return $this->belongsToMany(User::class, 'sys_user_groups', 'group_id', 'user_id');
    }
}
