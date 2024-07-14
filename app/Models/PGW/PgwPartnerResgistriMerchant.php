<?php

namespace App\Models\PGW;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PgwPartnerResgistriMerchant extends Model
{
    use HasFactory;
    protected $table = 'pgw_partner_resgistri_merchants';

    protected $fillable = [
        'partner_code',
        'payment_merchant_id',
        'sort',
        'business',
        'created_by',
        'updated_by'
    ];
    public function merchant()
    {
        return $this->belongsTo(PgwPaymentMerchants::class, 'payment_merchant_id');
    }
}
