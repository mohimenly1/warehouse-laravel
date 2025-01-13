<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateUserTypeEnumInUsersTable extends Migration
{
    public function up()
    {
        // Add the new value 'client' to the ENUM column
        DB::statement("ALTER TABLE `users` MODIFY `user_type` ENUM('trader', 'normal', 'admin', 'staff', 'client') DEFAULT 'normal'");
    }

    public function down()
    {
        // Rollback: Remove 'client' from the ENUM column
        DB::statement("ALTER TABLE `users` MODIFY `user_type` ENUM('trader', 'normal', 'admin', 'staff') DEFAULT 'normal'");
    }
}
