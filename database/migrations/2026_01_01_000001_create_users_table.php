<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama', 100)->nullable();
            $table->string('email', 100)->nullable()->unique();
            $table->string('password', 255)->nullable();
            $table->string('role', 20)->default('user');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
