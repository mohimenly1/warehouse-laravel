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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->change(); // Increase precision to 15 digits
        });
    }
    
    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->decimal('amount', 8, 2)->change(); // Revert to original precision
        });
    }
};
