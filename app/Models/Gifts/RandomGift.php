<?php

namespace App\Models\Gifts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RandomGift extends Model
{
    use HasFactory;

    protected $table = 'random_gifts';

    const STATUS_ACTIVE   = 'active';
    const STATUS_INACTIVE = 'inactive';
    const TYPE_PRODUCT    = 'product';
    const TYPE_COUPON     = 'coupon';

    protected $fillable
        = [
            'id',
            'is_default',
            'landing_page_id',
            'full_name',
            'description',
            'thumb',
            'rate',
            'type',
            'sort',
            'status',
            'name',
            'quantity',
            'quantity_use',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ];
     public function giftCoupon() {
         return $this->belongsTo(GiftCoupon::class,'id','gift_id');
     }
}
