<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $table = 'paket';
    protected $primaryKey = 'idPaket';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('paket', function (Blueprint $table) {
            $table->bigIncrements('idPaket');
            $table->string('kodePaket')->index();
            $table->string('namaPaket',1000)->nullable();
            $table->string('pelaksana',255)->nullable();
            $table->double('nilaiKontrak', 15, 2)->nullable();
            $table->year('tahun')->nullable();
            $table->date('tglMulai')->nullable();
            $table->date('tglSelesai')->nullable();
            $table->string('kodeUser')->index();
            $table->enum('statusProgres', array('1','2'))->nullable();
            $table->timestamps();
            $table->foreign('kodeUser')->references('kodeUser')->on('user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket');
    }
};
