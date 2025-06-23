<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $table = 'user';
    protected $primaryKey = 'idUser';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user', function (Blueprint $table) {
            $table->bigIncrements('idUser');
            $table->string('kodeUser',10)->index();
            $table->string('namaUser',100)->nullable();
            $table->string('username',100)->unique();
            $table->string('password',255);
            $table->enum('role', array('Admin','Konsultan'))->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
