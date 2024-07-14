<?php

namespace App\Models\Gifts;

use App\Models\ContactLeadProcess;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RandomGiftContact extends Model
{
    use HasFactory;

    protected $table = 'random_gift_contacts';

    protected $fillable
        = [
            'landing_page_id',
            'contact_id',
            'ticket_id',
            'gift_id',
            'coupon_code',
            'user_id',
            'created_by',
            'updated_by',
        ];

    public function gift() {
        return $this->belongsTo(RandomGift::class,'gift_id');
    }
    public function contact() {
        return $this->belongsTo(ContactLeadProcess::class,'contact_id');
    }
    public function ticket() {
        return $this->belongsTo(Ticket::class,'ticket_id');
    }
}
