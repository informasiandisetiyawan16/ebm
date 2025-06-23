<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = User::orderBy('created_at', 'DESC')->paginate(1000);
        
        return view('user.index', compact('user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('user.create');
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
            'namaUser' => 'required',
            'username' => 'required',
            'password' => 'required',
            'role' => 'required'
        ]);

        $count = User::where('username', $request->username)->count();

        if ($count > 0) {
            $peringatan="Username Yang Anda Masukan Sudah ada Di Database";
        } else {
            $randomNumber = mt_rand(0, 9999);
            $kodeUser = "DPUPR" . str_pad($randomNumber, 3, "0", STR_PAD_LEFT);
            $user = User::create([
                'kodeUser' => $kodeUser,
                'namaUser' => $request->namaUser,
                'username' => $request->username,
                'password' => bcrypt($request->password),
                'role' => $request->role
            ]);
            $peringatan="Data Berhasil Disimpan";
        }
        return redirect(route('user.index'))->with(['success' => $peringatan]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($idUser)
    {
        $user = User::find($idUser);
        
        return view('user.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($idUser)
    {
        $user = User::find($idUser);
        //ini untuk menampilkan
        return view('user.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $idUser)
    {
        $this->validate($request, [
            'namaUser' => 'required',
            'username' => 'required',
            'role' => 'required'
        ]);

        $user = User::find($idUser);

        $count = User::where('username', $request->username)->count();

        if ($count > 1) {
            $peringatan="Username Yang Anda Masukan Sudah ada Di Database";
        } else {
            $user->update([
                'namaUser' => $request->namaUser,
                'username' => $request->username,
                'role' => $request->role
            ]);
            $peringatan="Data Berhasil Disimpan";
        }
        
        
        return redirect(route('user.index'))->with(['success' => $peringatan]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($idUser)
    {
        $user = User::find($idUser); 
        $user->delete();
        return redirect(route('user.index'))->with(['success' => 'Data User Berhasil Dihapus']);
    }
}
