<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Where an invoice stood at one point in its life: approval, then each payment.
 */
class InvoiceRevision extends Model
{
    public const EVENT_APPROVED = 'approved';
    public const EVENT_PAYMENT = 'payment';

    protected $fillable = [
        'invoice_id',
        'payment_id',
        'user_id',
        'revision_number',
        'event',
        'total',
        'amount_received',
        'balance',
        'note',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'amount_received' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isSettled(): bool
    {
        return (float) $this->balance <= 0.0;
    }

    public function label(): string
    {
        return $this->event === self::EVENT_APPROVED ? 'Approved' : 'Payment received';
    }
}
