<?php

namespace App\Models\Gifts;

use App\Models\ContactLeadProcess;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $table = 'ticket';

    const STATUS_NEW      = 'new';
    const STATUS_VERIFIED = 'verified';
    const STATUS_APPROVED = 'approved';
    const BILL_CODE_VALID = 'Valid';
    const NO_LOCK = 'no';
    const YES_LOCK = 'yes';
    const NO_SCAN = 'no';
    const YES_SCAN = 'yes';

    protected $fillable
        = [
            'id',
            'landing_page_id',
            'contact_lead_process_id',
            'bill_code',
            'bill_value',
            'store_name',
            'status',
            'lock',
            'scan',
            'scan_number',
            'created_at',
            'updated_at',
        ];
    public function contact()
    {
        return $this->belongsTo(ContactLeadProcess::class, 'contact_lead_process_id');
    }
}
