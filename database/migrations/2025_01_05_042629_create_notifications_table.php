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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // User who receives the notification
            $table->unsignedBigInteger('rent_client_id'); // Rent request associated with the notification
            $table->string('message'); // Notification message
            $table->boolean('is_read')->default(false); // Whether the notification is read
            $table->timestamps();
    
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('rent_client_id')->references('id')->on('rent_clients')->onDelete('cascade');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
