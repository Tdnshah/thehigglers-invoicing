<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationNote extends Model
{
    protected $fillable = [
        'quotation_id',
        'user_id',
        'note',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
