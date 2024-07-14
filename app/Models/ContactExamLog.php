<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactExamLog extends Model
{
    use HasFactory;
    use Compoships;
    protected $table = 'contact_exam_logs';

    protected $fillable
        = [
            'contact_lead_process_id',
            'session_id',
            'question_id',
            'question_name',
            'result',
            'score',
            'time',
            'created_at',
        ];
    public function contact()
    {
        return $this->belongsTo(ContactLeadProcess::class, 'contact_lead_process_id');
    }
}
