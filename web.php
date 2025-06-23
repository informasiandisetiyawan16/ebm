<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaketController;
use App\Http\Controllers\ProgresController;
use App\Http\Controllers\ProgresFisikController;
use App\Http\Controllers\TargetProgresFisikController;
use App\Http\Controllers\StatistikController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/', 'App\Http\Controllers\AuthController@index')->name('index');
Route::post('proseslogin', 'App\Http\Controllers\AuthController@proseslogin')->name('proseslogin');
Route::get('logout', 'App\Http\Controllers\AuthController@logout')->name('logout');

Route::group(['middleware' => ['auth', 'cekLogin:Admin']], function () {
    Route::resource('user', UserController::class);
});

Route::group(['middleware' => ['auth', 'cekLogin:Admin,Konsultan']], function () {
    Route::get('home', 'App\Http\Controllers\HomeController@index')->name('home');
    Route::resource('auth', AuthController::class);
    Route::resource('paket', PaketController::class);
    Route::resource('progres', ProgresController::class);
    Route::resource('progresfisik', ProgresFisikController::class);
    Route::resource('targetprogresfisik', TargetProgresFisikController::class);
    Route::resource('statistik', StatistikController::class);
});