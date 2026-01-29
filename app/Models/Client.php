<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'address',
        'gst_number',
        'currency',
        'custom_fields',
    ];

    protected $casts = [
        'custom_fields' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class); // The Admin who owns this client
    }

    public function clientUser()
    {
        return $this->hasOne(User::class, 'client_id'); // The Client User Login
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
