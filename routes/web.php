<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GpsIntegrationController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'authenticate'])->name('login.attempt');
    Route::get('register', [RegisterController::class, 'show'])->name('register');
    Route::post('register', [RegisterController::class, 'register'])->name('register.perform');
});

Route::post('logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Viewing the vehicle list is open to all authenticated users (needed when booking).
    // Creating, editing, and deleting vehicles requires fleet_manager or above.
    Route::get('vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::middleware('role:fleet_manager,organization_admin,super_admin')->group(function () {
        Route::get('vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
        Route::post('vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
        Route::get('vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
        Route::put('vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
        Route::patch('vehicles/{vehicle}', [VehicleController::class, 'update']);
        Route::delete('vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
    });

    // All authenticated users can list, create, view, and cancel their own bookings.
    // Approving / rejecting (PUT with status payload) requires supervisor or above.
    Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::delete('bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
    Route::middleware('role:supervisor,fleet_manager,organization_admin,super_admin')
        ->group(function () {
            Route::put('bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
            Route::patch('bookings/{booking}', [BookingController::class, 'update']);
        });

    // Organization management — super_admin only.
    Route::middleware('role:super_admin')->group(function () {
        Route::resource('organizations', OrganizationController::class)->except(['show']);
    });

    // User management — organization_admin and super_admin only.
    Route::middleware('role:organization_admin,super_admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });

    // Reports — supervisor and above.
    Route::middleware('role:supervisor,fleet_manager,organization_admin,super_admin')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/bookings', [ReportController::class, 'bookings'])->name('reports.bookings');
        Route::get('reports/utilization', [ReportController::class, 'utilization'])->name('reports.utilization');
    });

    // GPS integrations — fleet_manager and above can manage; all auth users can view the map.
    Route::get('gps/map', [GpsIntegrationController::class, 'map'])->name('gps.map');
    Route::middleware('role:fleet_manager,organization_admin,super_admin')->group(function () {
        Route::get('gps', [GpsIntegrationController::class, 'index'])->name('gps.index');
        Route::get('gps/create', [GpsIntegrationController::class, 'create'])->name('gps.create');
        Route::post('gps', [GpsIntegrationController::class, 'store'])->name('gps.store');
        Route::get('gps/{gps}/edit', [GpsIntegrationController::class, 'edit'])->name('gps.edit');
        Route::put('gps/{gps}', [GpsIntegrationController::class, 'update'])->name('gps.update');
        Route::patch('gps/{gps}', [GpsIntegrationController::class, 'update']);
        Route::delete('gps/{gps}', [GpsIntegrationController::class, 'destroy'])->name('gps.destroy');
    });
});
