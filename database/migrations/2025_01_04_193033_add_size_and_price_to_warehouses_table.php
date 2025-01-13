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
        Schema::table('warehouses', function (Blueprint $table) {
            $table->decimal('width', 8, 2)->nullable(); // Width in meters
            $table->decimal('height', 8, 2)->nullable(); // Height in meters
            $table->decimal('price_per_meter', 8, 2)->nullable(); // Price per square meter
        });
    }
    
    public function down()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn(['width', 'height', 'price_per_meter']);
        });
    }
};
