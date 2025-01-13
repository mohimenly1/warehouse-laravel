<?php

// TicketController.php
namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    // Create a new ticket
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'user_id' => 'required|exists:users,id', // Validate that user_id exists in the users table
        ]);

        $ticket = Ticket::create([
            'user_id' => $request->user_id, // Use user_id from the request
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Ticket created successfully.',
            'ticket' => $ticket,
        ], 201);
    }

    // Get all tickets for the logged-in user
    public function index(Request $request)
    {
        $query = Ticket::with('responses.admin'); // Include responses with admin details

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $tickets = $query->get();
        return response()->json($tickets);
    }
}