<?php

namespace App\Models\PGW;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class  PgwOrderDetail extends Model
{
    use HasFactory;

    protected $table = 'pgw_order_details';

    protected $fillable = [
        'order_id',
        'product_id',
        'product_type',
        'product_name',
        'description',
        'quantity',
        'price',
        'discount',
        'is_refund',
        'created_by',
        'updated_by'
    ];
}
