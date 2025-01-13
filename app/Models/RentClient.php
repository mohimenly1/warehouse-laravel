<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentClient extends Model
{
    protected $fillable = [
        'user_id',
        'warehouse_id',
        'width',
        'height',
        'total_price',
        'active', // Add active column
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}