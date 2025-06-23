<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Paket;
use App\Models\Progres;
use DateTime;

class StatistikController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $idUser = auth()->guard('web')->user()->idUser;

        //Ambil role dan kodeUser dengan satu query
        $user = User::select('role', 'kodeUser')->where('idUser', '=', $idUser)->first();
        $role = $user->role;
        $kodeUser = $user->kodeUser;
            
        $progres = DB::table(DB::raw("
            (SELECT 
            progres.tgl, 
            AVG(progres.targetFisik) AS rataTargetFisikPerTanggal, 
            AVG(progres.realisasiFisik) AS rataRealisasiFisikPerTanggal,
    
            -- Menghitung total keseluruhan hanya 1 kali agar tidak salah perhitungan
            (SELECT SUM(targetFisik) FROM progres) AS totalTargetFisikKeseluruhan,
            (SELECT SUM(realisasiFisik) FROM progres) AS totalRealisasiFisikKeseluruhan
    
        FROM progres
        INNER JOIN paket ON progres.kodePaket = paket.kodePaket
        GROUP BY progres.tgl
        ) as subquery"))
        ->orderBy('subquery.tgl', 'ASC')
        ->paginate(100000);

        return view('statistik.index', compact('progres'));
    }
}
