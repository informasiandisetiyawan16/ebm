<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Paket;
use App\Models\Progres;
use DateTime;

class PaketController extends Controller
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

        if ($role == 'Admin') {
            $paket = Paket::orderBy('created_at', 'DESC')->paginate(1000);
        }elseif($role == 'KonsultanPengawas' || $role == 'Pengawas'){
            $paket = Paket::where('kodeUser', $kodeUser)->orderBy('created_at', 'DESC')->paginate(1000);
        }
        return view('paket.index', compact('paket'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = User::orderBy('created_at', 'DESC')->where('role','Konsultan')->paginate(1000);
        return view('paket.create', compact('user'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'namaPaket' => 'required',
            'pelaksana' => 'required',
            'nilaiKontrak' => 'required',
            'tahun' => 'required',
            'tglMulai' => 'required',
            'tglSelesai' => 'required'
        ]);

        //KodePaket
        do {
            $randomNumber = mt_rand(0, 9999);
            $formattedNumber = str_pad($randomNumber, 4, '0', STR_PAD_LEFT);
            $randomCode = "DPUPR" . $formattedNumber;
            $exists = Paket::where('kodePaket', $randomCode)->exists();
        } while ($exists);
        $kodePaket=$randomCode;

        $statusProgres='2';
        $year=2025;

        $paket = Paket::create([
            'kodePaket' => $kodePaket,
            'namaPaket' => $request->namaPaket,
            'pelaksana' => $request->pelaksana,
            'nilaiKontrak' => $request->nilaiKontrak ? str_replace('.', '', $request->nilaiKontrak) : null,
            'tahun' => $request->tahun,
            'tglMulai' => $request->tglMulai,
            'tglSelesai' => $request->tglSelesai,
            'kodeUser' => $request->kodeUser,
            'statusProgres' => $statusProgres,
        ]);
                
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

        $pesan="Data Berhasil Di Simpan";
                
        return redirect(route('paket.index'))->with(['success' => $pesan ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($idPaket)
    {
        $paket = Paket::with('user')->find($idPaket);

        //Progres Fisik
        $progres = Progres::select('progres.*', 'paket.*') 
        ->join('paket', 'progres.kodePaket', '=', 'paket.kodePaket') // Gabungkan dengan tabel paket
        ->where('paket.idPaket', '=', $idPaket) // Filter berdasarkan idPaket
        ->whereBetween('progres.tgl', [
            DB::raw('paket.tglMulai'), 
            DB::raw('DATE_ADD(paket.tglSelesai, INTERVAL 7 DAY)')
        ])
        ->orderBy('paket.idPaket', 'ASC')
        ->paginate(1000); 
       
        return view('paket.show', compact('paket','progres'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($idPaket)
    {
        $user = User::orderBy('created_at', 'DESC')->where('role','Konsultan')->paginate(1000);
        $paket = Paket::find($idPaket);
        //ini untuk menampilkan
        return view('paket.edit', compact('paket','user'));
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
        $this->validate($request, [
            'namaPaket' => 'required',
            'pelaksana' => 'required',
            'nilaiKontrak' => 'required',
            'tahun' => 'required',
            'tglMulai' => 'required',
            'tglSelesai' => 'required'
        ]);

        $paket = Paket::find($idPaket);
        $paket->update([
            'namaPaket' => $request->namaPaket,
            'pelaksana' => $request->pelaksana,
            'nilaiKontrak' => $request->nilaiKontrak ? str_replace('.', '', $request->nilaiKontrak) : null,
            'tahun' => $request->tahun,
            'tglMulai' => $request->tglMulai,
            'tglSelesai' => $request->tglSelesai,
            'kodeUser' => $request->kodeUser,
        ]);
        $pesan="Data Berhasil Di Ubah";
        
        return redirect(route('paket.index'))->with(['success' => $pesan ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($idPaket)
    {
        $kodePaket = Paket::where('idPaket','=',$idPaket)->value('kodePaket');
        $progres = Progres::where('kodePaket',$kodePaket); 
        $progres->delete();

        $paket = Paket::find($idPaket); 
        $paket->delete();
        return redirect(route('paket.index'))->with(['success' => 'Data Paket Berhasil Dihapus']);
    }
}
