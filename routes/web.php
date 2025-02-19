<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\AditController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AppliedAditController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AttendanceDetailsController;
use App\Http\Controllers\DailyAttendanceController;

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
    Route::get('/top', [IndexController::class, 'index'])->name('top');
    Route::get('/staff', [StaffController::class, 'index'])->name('staff');
    Route::get('/staffDetail', [StaffController::class, 'detail'])->name('staffDetail');
    Route::get('/staffEdit', [StaffController::class, 'edit'])->name('staffEdit');
    Route::put('/staff/update/{id}', [StaffController::class, 'update'])->name('staffUpdate');
    Route::get('/attendanceList', [AttendanceController::class, 'attendanceList'])->name('attendanceList');
    Route::get('/attendanceDetail/{employeeId?}/{year?}/{month?}/{eventId?}', [AttendanceController::class, 'attendanceDetail'])->name('attendanceDetail');
    Route::get('/attendance/{date}/{employeeId}/{companyId}', [AttendanceDetailsController::class, 'showDetails'])->name('attendanceDetails');
    Route::get('/appliedAdit/{companyId}', [AppliedAditController::class, 'index'])->name('appliedAdit');
    Route::post('/adit/approve', [AppliedAditController::class, 'approveAdit'])->name('adit.approve');
    Route::post('/adit/reject', [AppliedAditController::class, 'rejectAdit'])->name('adit.reject');
    Route::get('/attendance/export', [AttendanceController::class, 'exportAttendanceList'])->name('attendance.export');
    Route::get('/calendar', [AttendanceController::class, 'showCalendar'])->name('showCalendar');
    Route::get('/attendance/daily/{date}', [DailyAttendanceController::class, 'showDailyAttendance'])->name('attendance.daily');
    Route::get('/eventAttendance', [EventController::class, 'show'])->name('events.show');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::get('/events/show', [EventController::class, 'index'])->name('events.index');
    Route::post('/events/delete/{event}', [EventController::class, 'delete'])->name('events.delete');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::post('/events/export', [EventController::class, 'export'])->name('eventAttendance.export');
    Route::post('/attendance/update/{id}', [AttendanceDetailsController::class, 'update'])->name('attendance.update');
    Route::post('/attendance/delete/{id}', [AttendanceDetailsController::class, 'delete'])->name('attendance.delete');
    Route::post('/attendance/store', [AttendanceDetailsController::class, 'store'])->name('attendance.store');
    Route::get('/dailyAttendance/export', [DailyAttendanceController::class, 'exportDailyAttendance'])->name('dailyAttendance.export');
});
Route::middleware(['auth:employees', 'App\Http\Middleware\SessionTimeout'])->group(function () {
    Route::get('/adit', [AditController::class, 'index'])->name('adit');
    Route::get('/editAttendance', [AttendanceController::class, 'editAttendance'])->name('editAttendance');
    Route::post('/adit', [AditController::class, 'adit'])->name('adit');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
    Route::post('/updateAttendance', [AttendanceController::class, 'updateAttendance'])->name('updateAttendance');
    Route::post('/deleteAttendance', [AttendanceController::class, 'deleteAttendance'])->name('deleteAttendance');
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::post('/resetPassword', [LoginController::class, 'resetPassword'])->name('password.reset');