<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $table = 'progres';
    protected $primaryKey = 'idProgres';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('progres', function (Blueprint $table) {
            $table->bigIncrements('idProgres');
            $table->string('kodePaket')->index();
            $table->date('tgl')->nullable();
            $table->double('targetFisik', 15, 2)->nullable();
            $table->double('realisasiFisik', 15, 2)->nullable();
            $table->string('foto1',255)->nullable();
            $table->string('foto2',255)->nullable();
            $table->string('foto3',255)->nullable();
            $table->timestamps();
            $table->foreign('kodePaket')->references('kodePaket')->on('paket');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progres');
    }
};
