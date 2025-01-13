<?php

// TicketResponse.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'admin_id',
        'response',
    ];

    // Relationship to the ticket
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    // Relationship to the admin who responded
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}