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
        Schema::table('rent_clients', function (Blueprint $table) {
            $table->boolean('active')->default(true); // Active status of the rent request
        });
    }
    
    public function down()
    {
        Schema::table('rent_clients', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }
};
