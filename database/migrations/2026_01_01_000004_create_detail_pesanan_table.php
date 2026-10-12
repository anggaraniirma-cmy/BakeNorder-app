<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('detail_pesanan', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pesanan_id')->nullable()->index();
            $table->unsignedInteger('produk_id')->nullable()->index();
            $table->integer('jumlah')->nullable();
            $table->integer('subtotal')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('detail_pesanan');
    }
};
