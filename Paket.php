<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paket extends Model
{
    use HasFactory;
    protected $table = 'paket';
    protected $primaryKey = 'idPaket';
    protected $guarded=[];

    public function progres()
    {
        return $this->hasMany(Progres::class, 'kodePaket','kodePaket');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'kodeUser','kodeUser');
    }
}
