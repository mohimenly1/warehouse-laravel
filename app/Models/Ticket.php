<?php

// Ticket.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
    ];

    // Relationship to the user who created the ticket
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship to the ticket responses
    public function responses()
    {
        return $this->hasMany(TicketResponse::class);
    }
}