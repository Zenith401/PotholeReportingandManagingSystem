<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\Auth\RegisteredUserController;

// Public Routes
Route::get('/', function () {
    return view('home');
});

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/qgis/index.html', function () {
    return view('qgis.index');
});

// Registration Routes
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');
Route::get('/get-zip-codes/{cityId}', [RegisteredUserController::class, 'getZipCodes'])->name('register.zip-codes');

// AJAX Routes for Dependent Dropdowns
Route::get('/states/{countryId}', [RegisteredUserController::class, 'getStates'])->name('register.states');
Route::get('/cities/{stateId}', [RegisteredUserController::class, 'getCities'])->name('register.cities');
Route::get('/get-zip-codes/{cityId}', [RegisteredUserController::class, 'getZipCodes'])->name('register.zip-codes');

// Email Verification Notice
Route::get('/email/verify', function () {
    if (!session('status')) {
        session()->flash('status', 'verification-link-sent');
    }
    \Log::info('Session status set in /email/verify route', session()->all());
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// Routes requiring authentication and verified users
Route::middleware(['auth', 'verified'])->group(function () {
    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/update-location', [ProfileController::class, 'updateLocation'])->name('profile.update-location');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Location API Routes for Profile
    Route::get('/profile/states/{countryIso2}', [ProfileController::class, 'getStates'])->name('profile.states');
    Route::get('/profile/cities/{stateIso2}', [ProfileController::class, 'getCities'])->name('profile.cities');
    Route::get('/profile/zip-codes/{cityId}', [ProfileController::class, 'getZipCodes'])->name('profile.zip-codes');

    // Video Upload
    Route::get('/upload_video', [UploadController::class, 'upload'])->name('upload');
    Route::post('/upload_video', [UploadController::class, 'uploadPost'])->name('upload.post');
});

// Super Admin Routes
Route::middleware(['auth', 'verified', 'SuperAdmin'])->group(function () {
    Route::get('sAdminDashboard', [SuperAdminController::class, 'sAdminDashboard'])->name('sAdminDashboard');
    Route::get('view_users', [SuperAdminController::class, 'sAdminViewUsers'])->name('sAdminViewUsers');
    Route::get('view_images', [SuperAdminController::class, 'sAdminViewImages'])->name('sAdminViewImages');
    Route::delete('/users/{user}', [SuperAdminController::class, 'deleteUser'])->name('deleteUser');
    Route::post('/users/{user}/edit', [SuperAdminController::class, 'editUser'])->name('editUser');
});

// Admin Routes
Route::middleware(['auth', 'verified', 'Admin'])->group(function () {
    Route::get('adminDashboard', [AdminController::class, 'adminDashboard'])->name('adminDashboard');
});

// General User Routes
Route::middleware(['auth', 'verified', 'General'])->group(function () {
    Route::get('dashboard', [GeneralController::class, 'dashboard'])->name('dashboard');
});

// Dynamic Location Routes
Route::prefix('location')->group(function () {
    Route::get('/states/{countryIso2}', [LocationController::class, 'getStates'])->name('location.states');
    Route::get('/cities/{stateIso2}', [LocationController::class, 'getCities'])->name('location.cities');
    Route::get('/zip-codes/{cityId}', [LocationController::class, 'getZipCodes'])->name('location.zip-codes');
    Route::get('/countries', [LocationController::class, 'getCountries'])->name('location.countries');
});

// Include Authentication Routes
require __DIR__ . '/auth.php';
