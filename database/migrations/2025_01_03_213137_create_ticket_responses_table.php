<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// CreateTicketResponsesTable Migration
public function up(): void
{
    Schema::create('ticket_responses', function (Blueprint $table) {
        $table->id();
        $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade'); // Ticket being responded to
        $table->foreignId('admin_id')->constrained('users')->onDelete('cascade'); // Admin responding to the ticket
        $table->text('response'); // Admin's response
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_responses');
    }
};
