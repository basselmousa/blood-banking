<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::group(['prefix' => 'admin', 'as' => 'admin.'], function (){
    Route::get('/', [\App\Http\Controllers\AdminController::class , 'index'])->name('home');
    Route::get('/donors', [\App\Http\Controllers\AdminController::class , 'donor_list'])->name('donors');
    Route::get('/users', [\App\Http\Controllers\AdminController::class , 'user_list'])->name('users');
    Route::get('/add', [\App\Http\Controllers\AdminController::class , 'add_donor_or_patient'])->name('add');
    Route::get('/patients', [\App\Http\Controllers\AdminController::class , 'patient_list'])->name('patients');
    Route::get('/requests', [\App\Http\Controllers\AdminController::class , 'home_donation_request_list'])->name('requests');
    Route::get('/delete', [\App\Http\Controllers\AdminController::class , 'delete_donor_or_patient'])->name('delete');
});
