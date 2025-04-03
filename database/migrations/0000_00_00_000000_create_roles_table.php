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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('level')->default(1); // User/Role permissions are only usable against users/roles with a level less than the role's level

            $table->boolean('read_all_teams')->default(false); // Allows the user to see content from all teams
            $table->boolean('write_all_teams')->default(false); // Allows the user to create/edit content from all teams

            $table->boolean('manage_users')->default(false); // Covers Teams/Users/Roles
            $table->boolean('manage_clients')->default(false); // Covers Clients/Groups
            $table->boolean('manage_settings')->default(false); // Covers Providers
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
