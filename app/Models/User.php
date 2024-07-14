<?php

namespace App\Models;

use App\Models\SYS\SysGroup;
use App\Models\SYS\SysUserLandingpage;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'owner',
        'address',
        'partner_code',
        'status',
        'profile_photo_path',
        'password',
        'google_id',
        'created_by',
        'updated_by'
    ];
    public function groups()
    {
        return $this->belongsToMany(SysGroup::class, 'sys_user_groups', 'user_id', 'group_id');
    }
    public function LandingPage()
    {
        return $this->belongsToMany(LandingPage::class, 'sys_user_landing_page', 'user_id', 'landing_page_id');
//        return $this->hasMany(SysUserLandingpage::class, 'user_id','id');
    }
}
