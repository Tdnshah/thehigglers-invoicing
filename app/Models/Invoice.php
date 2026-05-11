<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'client_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'subtotal',
        'cgst',
        'sgst',
        'igst',
        'total',
        'status',
        'notes',
        'currency',
        'exchange_rate',
        'invoice_type',
        'place_of_supply',
        'lut_number',
        'custom_fields',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'custom_fields' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
