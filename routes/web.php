<?php

use App\Http\Controllers\HorasPdfController;
use App\Livewire\Auth\Login;
use App\Livewire\Kiosk\Registro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/registro');
});

// Standalone login for the kiosk circuit (fichaje users are denied /admin/login).
Route::get('/login', Login::class)->name('login');

Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
})->name('logout');

// The kiosk punch screen. EnsureKioskAccess enforces role + active state on
// every request (remote invalidation) before the component is reached.
Route::get('/registro', Registro::class)
    ->middleware('kiosk')
    ->name('kiosk.registro');

// Admin-only PDF exports for the worked-hours reports. The "admin.access"
// middleware mirrors the panel gate (active admins only).
Route::middleware(['web', 'auth', 'admin.access'])
    ->prefix('admin/horas')
    ->name('admin.horas.')
    ->group(function () {
        Route::get('resumen.pdf', [HorasPdfController::class, 'resumen'])->name('resumen');
        Route::get('detalle.pdf', [HorasPdfController::class, 'detalle'])->name('detalle');
    });
