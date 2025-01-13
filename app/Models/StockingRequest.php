<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockingRequest extends Model
{
    protected $fillable = [
        'client_id',
        'warehouse_id',
        'status',
        'description', // Add this line
        'subscription_amount',
        'is_paid',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}