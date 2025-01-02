<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AditController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AppliedAditController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/attendance', function () {
    return view('attendance');
})->name('attendance');
Route::get('/change', function () {
    return view('change');
})->name('change');
Route::get('/staffCreate', function () {
    return view('staffCreate');
})->name('staffCreate');
Route::post('/employeeCreate', [StaffController::class, 'create'])->name('employeeCreate');
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
    Route::get('/staffDetail', [StaffController::class, 'detail'])->name('staffDetail');
    Route::get('/staffEdit', [StaffController::class, 'edit'])->name('staffEdit');
    Route::put('/staff/update/{id}', [StaffController::class, 'update'])->name('staffUpdate');
    Route::get('/attendanceList', [AttendanceController::class, 'attendanceList'])->name('attendanceList');
    Route::get('/attendanceDetail/{employeeId}/{year}/{month}', [AttendanceController::class, 'attendanceDetail'])->name('attendanceDetail');
    Route::get('/appliedAdit/{companyId}', [AppliedAditController::class, 'index'])->name('appliedAdit');
    Route::post('/adit/approve', [AppliedAditController::class, 'approveAdit'])->name('adit.approve');
    Route::post('/adit/reject', [AppliedAditController::class, 'rejectAdit'])->name('adit.reject');
});
Route::middleware('auth:employees')->group(function () {
    Route::get('/adit', [AditController::class, 'index'])->name('adit');
    Route::get('/editAttendance', [AttendanceController::class, 'editAttendance'])->name('editAttendance');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
    Route::post('/updateAttendance', [AttendanceController::class, 'updateAttendance'])->name('updateAttendance');
    Route::post('/deleteAttendance', [AttendanceController::class, 'deleteAttendance'])->name('deleteAttendance');
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::post('/adit', [AditController::class, 'adit'])->name('adit');