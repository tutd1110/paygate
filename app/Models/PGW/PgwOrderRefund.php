<?php

namespace App\Models\PGW;

use App\Models\LandingPage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PgwOrderRefund extends Model
{
    use HasFactory;
    protected $table = 'pgw_order_refunds';

    protected $fillable = [
        'order_id',
        'landing_page_id',
        'partner_code',
        'refund_value',
        'description',
        'status',
        'created_by',
        'updated_by'
    ];

    public function partner()
    {
        return $this->belongsTo(PgwPartner::class, 'partner_code', 'code');
    }

    public function landingPage()
    {
        return $this->belongsTo(LandingPage::class, 'landing_page_id');
    }

    public function Order()
    {
        return $this->belongsTo(PgwOrder::class, 'order_id');
    }
}
