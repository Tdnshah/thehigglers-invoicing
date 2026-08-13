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
        'approved_at',
        'approved_by',
        'terms_mode',
        'terms_conditions',
        'bank_mode',
        'bank_details',
        'bank_account_id',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'custom_fields' => 'array',
        'bank_details' => 'array',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Internal notes thread. Named privateNotes because `notes` is the legacy
     * single text column this thread replaced.
     */
    public function privateNotes()
    {
        return $this->hasMany(InvoiceNote::class)->latest();
    }

    /**
     * The approved quotation this invoice was cloned from, if any.
     * The link is held on the quotation side, in quotations.invoice_id.
     */
    public function quotation()
    {
        return $this->hasOne(Quotation::class, 'invoice_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Dated snapshots of the invoice's position, one per approval and payment.
     */
    public function revisions()
    {
        return $this->hasMany(InvoiceRevision::class)->orderBy('revision_number');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * A paid invoice is closed: no edits, no payments, no status changes.
     */
    public function isLocked(): bool
    {
        return $this->isPaid();
    }

    /**
     * Payments are only accepted between approval and full settlement.
     */
    public function acceptsPayments(): bool
    {
        return $this->isApproved() && ! $this->isPaid();
    }

    public function amountPaid(): float
    {
        return (float) ($this->relationLoaded('payments')
            ? $this->payments->sum('amount')
            : $this->payments()->sum('amount'));
    }

    public function balanceDue(): float
    {
        return round((float) $this->total - $this->amountPaid(), 2);
    }

    /**
     * Snapshot the invoice's position. Load the payments relation first when a
     * payment was just recorded, so the received figure includes it.
     */
    public function recordRevision(string $event, ?string $note = null, ?Payment $payment = null, ?int $userId = null): InvoiceRevision
    {
        $received = $this->amountPaid();

        return $this->revisions()->create([
            'payment_id' => $payment?->id,
            'user_id' => $userId,
            'revision_number' => ((int) $this->revisions()->max('revision_number')) + 1,
            'event' => $event,
            'total' => $this->total,
            'amount_received' => $received,
            'balance' => round((float) $this->total - $received, 2),
            'note' => $note,
        ]);
    }
}
