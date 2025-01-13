<?php

// AdminTicketController.php
namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketResponse;
use Illuminate\Http\Request;

class AdminTicketController extends Controller
{
    // Get all tickets (for admin)
    public function index()
    {
        $tickets = Ticket::with('user', 'responses.admin')->get();
        return response()->json($tickets);
    }

    // Respond to a ticket
    public function respond(Request $request, Ticket $ticket)
    {
        $request->validate([
            'response' => 'required|string',
            'admin_id' => 'required|exists:users,id', // Validate admin_id
        ]);

        // Create the response
        $response = TicketResponse::create([
            'ticket_id' => $ticket->id,
            'admin_id' => $request->admin_id, // Use admin_id from the request
            'response' => $request->response,
        ]);

        // Update the ticket status to "closed"
        $ticket->status = 'closed';
        $ticket->save();

        return response()->json([
            'message' => 'Response submitted successfully.',
            'response' => $response,
            'ticket' => $ticket, // Return the updated ticket
        ]);
    }
}