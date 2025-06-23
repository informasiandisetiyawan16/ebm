<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Paket;
use App\Models\Progres;
use DateTime;

class ProgresFisikController extends Controller
{
    public function edit($tgl)
    {
        $idUser=auth()->guard('web')->user()->idUser;

        $user = User::select('role', 'kodeUser')->where('idUser', '=', $idUser)->first();
        $role = $user->role;
        $kodeUser = $user->kodeUser;

        if ($role == 'Admin') {
            $progres = Progres::select('progres.*', 'paket.*')
            ->where('progres.tgl', '=', $tgl)
            ->join('paket', 'progres.kodePaket', '=', 'paket.kodePaket')
            ->orderBy('paket.idPaket', 'ASC') 
            ->paginate(1000);
        }else{
            $progres = Progres::select('progres.*', 'paket.*')
            ->where('progres.tgl', '=', $tgl)
            ->join('paket', 'progres.kodePaket', '=', 'paket.kodePaket')
            ->where('paket.kodeUser', $kodeUser)
            ->orderBy('paket.idPaket', 'ASC')
            ->paginate(1000);
        }
        //ini untuk menampilkan
        return view('progresfisik.edit', compact('progres','tgl'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $tgl) 
    {
        $idProgres = $request->idProgres;
        $targetFisik = $request->targetFisik;
        $realisasiFisik = $request->realisasiFisik;
        $foto1 = $request->file('foto1');
        $foto2 = $request->file('foto2');
        $foto3 = $request->file('foto3');

        foreach ($idProgres as $index => $id) {
            
            $kodePaket = Progres::where('idProgres', $id)->value('kodePaket');
            $dataToUpdate = [];

            // Update targetFisik jika tidak kosong
            if (!is_null($targetFisik[$index]) && $targetFisik[$index] !== '') {
                $dataToUpdate['targetFisik'] = $targetFisik[$index];
            }

            // Update realisasiFisik jika tidak kosong
            if (!is_null($realisasiFisik[$index]) && $realisasiFisik[$index] !== '') {
                $dataToUpdate['realisasiFisik'] = $realisasiFisik[$index];

                // Update progres berikutnya
                Progres::where('kodePaket', $kodePaket)
                    ->where('tgl', '>', $tgl)
                    ->update(['realisasiFisik' => $realisasiFisik[$index]]);
            }

            // Upload foto jika valid
            if (isset($foto1[$index]) && $foto1[$index]->isValid()) {
                $filePath = $foto1[$index]->store('progres', 'public');
                $dataToUpdate['foto1'] = $filePath;
            }

            if (isset($foto2[$index]) && $foto2[$index]->isValid()) {
                $filePath = $foto2[$index]->store('progres', 'public');
                $dataToUpdate['foto2'] = $filePath;
            }

            if (isset($foto3[$index]) && $foto3[$index]->isValid()) {
                $filePath = $foto3[$index]->store('progres', 'public');
                $dataToUpdate['foto3'] = $filePath;
            }

            // Update progres jika ada data yang berubah
            if (!empty($dataToUpdate)) {
                Progres::where('idProgres', $id)->update($dataToUpdate);
            }
        }

        return redirect(route('progresfisik.edit', $tgl))
            ->with(['success' => 'Data Progres berhasil diinput']);
    }

    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($tgl)
    {
        $idUser=auth()->guard('web')->user()->idUser;

        // Ambil role dan kodeUser dengan satu query
        $user = User::select('role', 'kodeUser')->where('idUser', '=', $idUser)->first();
        $role = $user->role;
        $kodeUser = $user->kodeUser;

        if ($role == 'Admin') {
            $progres = Progres::select(
                'progres.*', 
                'paket.*',
                DB::raw("(SELECT AVG(targetFisik) FROM progres WHERE progres.tgl = '$tgl') AS avgTargetFisik"),
                DB::raw("(SELECT AVG(realisasiFisik) FROM progres WHERE progres.tgl = '$tgl') AS avgRealisasiFisik")
            )
            ->where('progres.tgl', '=', $tgl)
            ->join('paket', 'progres.kodePaket', '=', 'paket.kodePaket')
            ->orderBy('paket.idPaket', 'ASC') 
            ->paginate(1000);

            // **Ambil rata-rata target dan realisasi fisik dari hasil query**
            $avgTargetFisik = $progres->first()->avgTargetFisik ?? 0;
            $avgRealisasiFisik = $progres->first()->avgRealisasiFisik ?? 0;
            
            // **Format agar hanya 2 angka di belakang koma**
            $avgTargetFisik = number_format($avgTargetFisik, 2, ',', '.');
            $avgRealisasiFisik = number_format($avgRealisasiFisik, 2, ',', '.');

        }else{
            $progres = Progres::select('progres.*', 'paket.*') // Hanya ambil kolom yang dibutuhkan
            ->where('progres.tgl', '=', $tgl) // Pastikan kolom ini diindeks
            ->join('paket', 'progres.kodePaket', '=', 'paket.kodePaket') // Indeks pada kedua kolom
            ->where('paket.kodeUser', $kodeUser) // Menambahkan filter berdasarkan konsultan
            ->orderBy('paket.idPaket', 'ASC') // Indeks pada kolom ini
            ->paginate(1000); // Pertimbangkan jumlah data per halaman
        }
        return view('progresfisik.show', compact('progres','tgl','avgTargetFisik','avgRealisasiFisik'));
    }
}
