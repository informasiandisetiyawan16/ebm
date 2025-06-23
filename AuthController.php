<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Models\User;

class AuthController extends Controller
{
    public function index()
    {
        if ($user = Auth::user()) {
                return redirect()->intended('home');
        }
        return view('index');
    }

    public function proseslogin(Request $request)
    {
        $login = [
            'username' => $request->username,
            'password' => $request->password
        ];

        if (auth()->attempt($login)) {
            return redirect()->route('home');
        }
        return redirect()->route('index')->with(['error' => 'Maaf Username / Password Anda salah!']);
    }

    public function logout(Request $request)
    {
       $request->session()->flush();
       Auth::logout();
       return Redirect('index');
    }

    public function update(Request $request, $idUser)
    {
        $this->validate($request, [
            'namaUser' => 'required'
            ]);

            $user = User::find($idUser);
        
            $user->update([
                'namaUser' => $request->namaUser
            ]);
            return redirect(route('home'))->with(['success' => 'Data Berhasil Diperbaharui']);
    }
}
