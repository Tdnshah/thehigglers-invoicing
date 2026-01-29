<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'gst_number',
        'address',
        'phone',
        'email',
        'website',
        'logo_path',
        'bank_name',
        'bank_account_number',
        'bank_ifsc',
        'custom_fields',
    ];

    protected $casts = [
        'custom_fields' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
