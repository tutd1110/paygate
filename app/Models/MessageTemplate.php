<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    use HasFactory;

    protected $table = 'message_templates';

    protected $fillable
        = [
            'template_name',
            'parent_id',
            'code',
            'content',
            'bind_param',
            'landing_page_id',
            'event',
            'status',
            'created_at',
            'updated_at'
        ];
    public function landingPage()
    {
        return $this->belongsTo(LandingPage::class, 'landing_page_id');
    }
}
