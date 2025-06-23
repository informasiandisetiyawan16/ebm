<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Paket;
use App\Models\Progres;
use DateTime;

class TargetProgresFisikController extends Controller
{
    public function edit($idPaket)
    {
        $idUser=auth()->guard('web')->user()->idUser;
        $role = User::where('idUser','=',$idUser)->value('role');
        $kodeUser = User::where('idUser','=',$idUser)->value('kodeUser');

        $namaPaket = Paket::where('idPaket','=',$idPaket)->value('namaPaket');
        $tglMulai = Paket::where('idPaket','=',$idPaket)->value('tglMulai');
        $tglSelesai = Paket::where('idPaket','=',$idPaket)->value('tglSelesai');

        $progres = Progres::select('progres.*', 'paket.*') // Ambil hanya kolom yang dibutuhkan
        ->join('paket', 'progres.kodePaket', '=', 'paket.kodePaket') // Gabungkan dengan tabel paket
        ->where('paket.idPaket', '=', $idPaket) // Filter berdasarkan idPaket
        ->whereBetween('progres.tgl', [
            DB::raw('paket.tglMulai'), 
            DB::raw('DATE_ADD(paket.tglSelesai, INTERVAL 7 DAY)')
        ]) // Memastikan tgl berada dalam rentang tglMulai hingga tglSelesai + 7 hari
        ->orderBy('paket.idPaket', 'ASC') // Urutkan berdasarkan idPaket
        ->paginate(1000); // Batasi jumlah data per halaman
    

        //ini untuk menampilkan
        return view('targetprogresfisik.edit', compact('progres','idPaket','namaPaket','tglMulai','tglSelesai'));
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
        $idProgres = $request->idProgres;
        $targetFisik = $request->targetFisik;

        // Ambil tanggal mulai dan tanggal selesai dari Paket
        $tglMulai = Paket::where('idPaket', $idPaket)->value('tglMulai');
        $tglSelesai = Paket::where('idPaket', $idPaket)->value('tglSelesai');

        // 1️⃣ **Update targetFisik menjadi 0 untuk progres sebelum tglMulai**
        Progres::where('kodePaket', function ($query) use ($idPaket) {
                $query->select('kodePaket')->from('paket')->where('idPaket', $idPaket);
            })
            ->where('tgl', '<', $tglMulai)
            ->update(['targetFisik' => 0]);

        // 2️⃣ **Update targetFisik menjadi 100 untuk progres setelah tglSelesai**
        Progres::where('kodePaket', function ($query) use ($idPaket) {
                $query->select('kodePaket')->from('paket')->where('idPaket', $idPaket);
            })
            ->where('tgl', '>', $tglSelesai)
            ->update(['targetFisik' => 100]);

        // 3️⃣ **Loop untuk update targetFisik sesuai input user**
        foreach ($idProgres as $index => $id) {
            $dataToUpdate = [];

            // Update targetFisik hanya jika tidak kosong
            if (!is_null($targetFisik[$index]) && $targetFisik[$index] !== '') {
                $dataToUpdate['targetFisik'] = $targetFisik[$index];
            }

            // Hanya update jika ada perubahan data
            if (!empty($dataToUpdate)) {
                Progres::where('idProgres', $id)->update($dataToUpdate);
            }
        }

        // Redirect kembali ke halaman edit dengan pesan sukses
        return redirect()->route('targetprogresfisik.edit', $idPaket)->with(['success' => 'Data Progres berhasil diupdate']);

    }
}
