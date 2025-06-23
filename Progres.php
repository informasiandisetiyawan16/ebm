<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Progres extends Model
{
    use HasFactory;
    protected $table = 'progres';
    protected $primaryKey = 'idProgres';
    protected $guarded=[];

    public function paket()
    {
        return $this->belongsTo(Paket::class, 'kodePaket','kodePaket');
    }
}
