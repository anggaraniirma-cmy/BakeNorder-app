<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->string('nama', 100)->nullable();
            $table->date('tanggal_jadi')->nullable();
            $table->time('jam_pengambilan')->nullable();
            $table->text('alamat')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('total')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->string('status', 20)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pesanan');
    }
};
