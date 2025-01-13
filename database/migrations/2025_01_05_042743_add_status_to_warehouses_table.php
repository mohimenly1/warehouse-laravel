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
            $table->string('status')->default('available'); // Status of the warehouse (available/busy)
        });
    }
    
    public function down()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
