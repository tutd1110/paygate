<?php

namespace App\Models\Gifts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftCoupon extends Model
{
    use HasFactory;

    protected $table = 'gift_coupon';


    protected $fillable
        = [
            'id',
            'gift_id',
            'code',
            'used',
            'created_at',
            'updated_at',
        ];

    public function randomGift()
    {
        return $this->belongsTo(RandomGift::class, 'gift_id');
    }
}
