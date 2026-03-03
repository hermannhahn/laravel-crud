<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPermissionController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\CompanyProfessionalController;
use App\Http\Controllers\TaskAreaController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // New Profile routes (Image, data, password)
    Route::get('/user-profile', [UserProfileController::class, 'edit'])->name('user-profile.edit');
    Route::post('/user-profile', [UserProfileController::class, 'update'])->name('user-profile.update');

    Route::resource('tasks', TaskController::class);
    Route::post('/tasks/{task}/respond', [TaskController::class, 'respond'])->name('tasks.respond');
    Route::post('/tasks/{task}/accept', [TaskController::class, 'accept'])->name('tasks.accept');
    Route::post('/tasks/{task}/release', [TaskController::class, 'release'])->name('tasks.release');
    Route::post('/tasks/{task}/unassign', [TaskController::class, 'unassign'])->name('tasks.unassign');

    Route::get('/professionals', [CompanyProfessionalController::class, 'index'])->name('professionals.index');
    Route::post('/professionals/add', [CompanyProfessionalController::class, 'addProfessional'])->name('professionals.add');
    Route::delete('/professionals/{user}', [CompanyProfessionalController::class, 'removeProfessional'])->name('professionals.remove');
    Route::patch('/professionals/{user}/permissions', [CompanyProfessionalController::class, 'updatePermissions'])->name('professionals.update-permissions');

    // Areas
    Route::get('/areas', [TaskAreaController::class, 'index'])->name('areas.index');
    Route::post('/areas', [TaskAreaController::class, 'store'])->name('areas.store');
    Route::delete('/areas/{area}', [TaskAreaController::class, 'destroy'])->name('areas.destroy');

    // Services
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
});

Route::middleware('admin')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/users/{user}/permissions', [UserPermissionController::class, 'edit'])->name('users.permissions.edit');
    Route::post('/users/{user}/permissions', [UserPermissionController::class, 'update'])->name('users.permissions.update');
});

require __DIR__.'/auth.php';
