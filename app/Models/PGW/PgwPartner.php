<?php

namespace App\Models\PGW;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PgwPartner extends Model
{
    use HasFactory;
    protected $table = 'pgw_partners';

    protected $fillable = [
        'code',
        'name',
        'status',
        'description',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];
    public function resgistriMerchant()
    {
        return $this->hasMany(PgwPartnerResgistriMerchant::class, 'partner_code','code');
    }
    public function registriBanking()
    {
        return $this->hasMany(PgwPartnerRegistriBanking::class, 'partner_code','code');
    }

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
}
