<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactExam extends Model
{
    use HasFactory;
    use Compoships;

    protected $table = 'contact_exams';

    protected $fillable
        = [
            'contact_lead_process_id',
            'session_id',
            'total_question',
            'total_score',
            'is_done',
            'total_time',
            'number',
            'created_at',
            'updated_at'
        ];
    public function contact()
    {
        return $this->belongsTo(ContactLeadProcess::class, 'contact_lead_process_id');
    }
    public function contactExamLogs()
    {
        return $this->hasMany(ContactExamLog::class,
            ['contact_lead_process_id','session_id'],
            ['contact_lead_process_id','session_id']
        );
    }
}
