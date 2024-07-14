<?php

namespace App\Models\Invoice;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $table = 'invoice_items';

    protected $fillable
        = [
            'invoice_id',
            'product_id',
            'product_type',
            'product_name',
            'quantity',
            'price',
            'discount',
            'created_by',
            'updated_by',
        ];

    protected $casts = [
        'price' => 'double',
        'discount' => 'double',
    ];

}
