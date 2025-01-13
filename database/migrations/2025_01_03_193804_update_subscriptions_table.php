<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('card_number')->nullable(); // Credit card number
            $table->string('expiration_date')->nullable(); // Expiration date (MM/YY)
            $table->string('cvv')->nullable(); // CVV
            $table->string('cardholder_name')->nullable(); // Cardholder name
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            //
        });
    }
};
