<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * total_price
     */ 
    public function up(): void 
    {
        Schema::table('rent_clients', function (Blueprint $table) {
            $table->decimal('total_price', 15, 2)->change(); // Increase precision to 15 digits
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rent_clients', function (Blueprint $table) {
            $table->decimal('total_price', 8, 2)->change(); // Revert to original precision
        });
    }
};
