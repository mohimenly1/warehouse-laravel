<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'package_id', // Add this line
        'subscription_type',
        'amount',
        'payment_method',
        'voucher_code',
        'paid_at',
        'is_paid',
        'card_number',
        'expiration_date',
        'cvv',
        'cardholder_name',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class); // Add this relationship
    }
}
