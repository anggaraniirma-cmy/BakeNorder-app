<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pesanan_id')->nullable()->unique();
            $table->string('bukti_transfer', 255)->nullable();
            $table->timestamp('tanggal_bayar')->useCurrent();
            $table->string('metode_pembayaran', 50)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pembayaran');
    }
};
