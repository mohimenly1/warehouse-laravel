<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('rent_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->decimal('width', 8, 2); // Rented width in meters
            $table->decimal('height', 8, 2); // Rented height in meters
            $table->decimal('total_price', 8, 2); // Total price for the rented space
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rent_clients');
    }
};
