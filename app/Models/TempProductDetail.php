<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class TempProductDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'warehouse_id',
        'description',
        'quantity',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}