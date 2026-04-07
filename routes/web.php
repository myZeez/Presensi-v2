<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\EmployeeAuthController;

// Karyawan Auth Route
Route::get('/login', [EmployeeAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [EmployeeAuthController::class, 'login'])->name('employee.login.post');
Route::post('/logout', [EmployeeAuthController::class, 'logout'])->name('employee.logout');

// Proteksi Halaman Presensi untuk Karyawan
Route::middleware('auth:employee')->group(function () {
    Route::get('/', function () {
        return view('welcome');
    })->name('home');
    Route::post('/attendance', [EmployeeAuthController::class, 'storeAttendance'])->name('employee.attendance.store');
});

// Admin Auth Route
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});

// Ruang lingkup Admin sederhana untuk kelola karyawan dan rekap data
Route::middleware('auth:web')->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/employees', [AdminController::class, 'employees'])->name('admin.employees');
    Route::post('/employees', [AdminController::class, 'storeEmployee'])->name('admin.employees.store');
    Route::delete('/employees/{employee}', [AdminController::class, 'destroyEmployee'])->name('admin.employees.destroy');

    Route::get('/attendances', [AdminController::class, 'attendances'])->name('admin.attendances');
});
