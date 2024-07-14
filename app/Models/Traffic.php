<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Traffic extends Model
{
    use HasFactory;
    use Compoships;

    protected $table = 'traffics';

    protected $fillable
        = [
            'id',
            'landing_page_id',
            'user_id',
            'campaign_id',
            'cookie_id',
            'session_id',
            'uri',
            'query_string',
            'utm_medium',
            'utm_source',
            'utm_campaign_id',
            'utm_content_id',
            'utm_creator_id',
            'utm_medium_id',
            'utm_source_id',
            'utm_term_id',
            'utm_campaign',
            'register_ip',
        ];

    public function landingPage()
    {
        return $this->belongsTo(LandingPage::class, 'landing_page_id');
    }

    public function contacts()
    {
        return $this->hasMany(ContactLeadProcess::class, [
            'utm_medium',
            'utm_source',
            'utm_campaign',
            'landing_page_id'
        ], [
            'utm_medium',
            'utm_source',
            'utm_campaign',
            'landing_page_id',
        ]);
    }
}
