<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Paket;
use App\Models\Progres;
use DateTime;
use Carbon\Carbon;

class ProgresController extends Controller
{
     /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
     public function index()
     {

        $progres = Progres::select('tgl')
            ->whereDate('tgl', '<=', Carbon::now()->addDay()) // Tambahkan 1 hari ke tanggal sekarang
            ->groupBy('tgl')
            ->orderBy('tgl', 'DESC')
            ->get();

          return view('progres.index', compact('progres'));
     }

     /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($idPaket)
    {
        $paket = Paket::find($idPaket);
        //ini untuk menampilkan
        return view('progres.edit', compact('paket'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $idPaket)
    {
        $paket = Paket::find($idPaket);
        $kodePaket = Paket::where('idPaket','=',$idPaket)->value('kodePaket');
        
        // Fungsi untuk mendapatkan semua tanggal hari Minggu di tahun tertentu
        function getSundays($year) {
            $sundays = [];
            $startDate = new DateTime("$year-01-01");
            $endDate = new DateTime("$year-12-31");

            // Cari hari Minggu pertama
            while ($startDate->format('w') != 0) {
                $startDate->modify('+1 day');
            }

            // Tambahkan semua hari Minggu ke dalam array
            while ($startDate <= $endDate) {
                $sundays[] = $startDate->format('Y-m-d');
                $startDate->modify('+7 days');
            }

            // Tambahkan tanggal 31 Desember 2025 jika belum ada
            $lastDate = "$year-12-31";
            if (!in_array($lastDate, $sundays)) {
                $sundays[] = $lastDate;
            }

            return $sundays;
        }

        // Dapatkan semua tanggal hari Minggu di tahun 2025
        $sundays = getSundays(2025);

        // Simpan semua tanggal ke tabel Progres
        foreach ($sundays as $tgl) {
            Progres::create([
                'kodePaket' => $kodePaket,
                'tgl' => $tgl
            ]);
        }

        $statusProgres='2';
        $paket->update([
            'statusProgres' => $statusProgres
        ]);
        $pesan="Data Progres Tahun 2025 Berhasil Di Buat";
        
        return redirect(route('paket.index'))->with(['success' => $pesan ]);
    }
}
