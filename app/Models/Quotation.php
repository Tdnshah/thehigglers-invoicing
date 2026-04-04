<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quotation extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'client_id',
        'parent_id',
        'revision_number',
        'quotation_number',
        'quotation_date',
        'valid_until',
        'quotation_type',
        'place_of_supply',
        'currency',
        'subtotal',
        'cgst',
        'sgst',
        'igst',
        'total',
        'status',
        'client_notes',
        'terms_conditions',
        'invoice_id',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
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
        return $this->hasMany(QuotationItem::class);
    }

    public function notes()
    {
        return $this->hasMany(QuotationNote::class);
    }

    public function parent()
    {
        return $this->belongsTo(Quotation::class, 'parent_id');
    }

    public function revisions()
    {
        return $this->hasMany(Quotation::class, 'parent_id')->orderBy('revision_number', 'asc');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isConverted()
    {
        return $this->invoice_id !== null;
    }

    /**
     * Check if the entire quotation tree (root + revisions) has an approved version.
     */
    public function isLocked()
    {
        $rootId = $this->parent_id ?? $this->id;
        return Quotation::where('id', $rootId)
            ->orWhere('parent_id', $rootId)
            ->where('status', 'approved')
            ->exists();
    }

    /**
     * Get the approved version in the current tree, if one exists.
     */
    public function getApprovedRevision()
    {
        $rootId = $this->parent_id ?? $this->id;
        return Quotation::where('id', $rootId)
            ->orWhere('parent_id', $rootId)
            ->where('status', 'approved')
            ->first();
    }
}
