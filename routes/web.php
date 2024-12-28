<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\StaffController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/attendance', function () {
    return view('attendance');
})->name('attendance');
Route::get('/change', function () {
    return view('change');
})->name('change');
Route::get('/staffDetail', function () {
    return view('staffDetail');
})->name('staffDetail');
Route::get('/staffEdit', function () {
    return view('staffEdit');
})->name('staffEdit');
Route::get('/staffCreate', function () {
    return view('staffCreate');
})->name('staffCreate');
Route::post('/employeeCreate', [StaffController::class, 'create'])->name('employeeCreate');
Route::get('/attendance2', function () {
    return view('attendance2');
})->name('attendance2');
Route::get('/attendance3', function () {
    return view('attendance3');
})->name('attendance3');
Route::get('/passwordReset', function () {
    return view('passwordReset');
})->name('passwordReset');
Route::get('/register', function () {
    return view('register');
})->name('register');
Route::get('/complete', function () {
    return view('complete');
})->name('complete');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::middleware(['auth'])->group(function () {
    Route::get('/staff', [StaffController::class, 'index'])->name('staff');
});
Route::middleware('auth:employees')->group(function () {
    Route::get('/adit', function () {
        return view('adit');
    })->name('adit');
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');