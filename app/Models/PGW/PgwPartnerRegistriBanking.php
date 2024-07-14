<?php

namespace App\Models\PGW;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PgwPartnerRegistriBanking extends Model
{
    use HasFactory;
    protected $table = 'pgw_partner_registri_bankings';
    protected $fillable =
        [
            'id',
            'code',
            'banking_list_id',
            'description',
            'partner_code',
            'thumb_path',
            'owner',
            'bank_number',
            'branch',
            'business',
            'type',
            'sort',
            'created_by',
            'updated_by',
        ];
    public function bankingList()
    {
        return $this->belongsTo(PgwBankingList::class, 'banking_list_id');
    }
}
