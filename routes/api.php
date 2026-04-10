<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AspirasiController;
use App\Http\Controllers\InputAspirasiController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\SaranPublikController;
use App\Http\Controllers\StatistikController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Auth routes
Route::post('/siswa/login', [SiswaController::class, 'login']);
Route::post('/admin/login', [AdminController::class, 'login']);

// Siswa routes
Route::patch('/siswa/{nis}/toggle-active', [SiswaController::class, 'toggleActive']);
Route::apiResource('siswa', SiswaController::class)->parameters(['siswa' => 'nis']);

// Admin routes
Route::apiResource('admin', AdminController::class)->parameters(['admin' => 'id']);

// Kategori routes
Route::apiResource('kategori', KategoriController::class)->parameters(['kategori' => 'id']);

// Input Aspirasi routes
Route::get('/input-aspirasi/recent', [InputAspirasiController::class, 'recent']);
Route::get('/input-aspirasi/siswa/{nis}', [InputAspirasiController::class, 'getBySiswa']);
Route::apiResource('input-aspirasi', InputAspirasiController::class)->parameters(['input-aspirasi' => 'id']);

// Aspirasi routes
Route::patch('/aspirasi/{id}/status', [AspirasiController::class, 'updateStatus']);
Route::patch('/aspirasi/{id}/feedback', [AspirasiController::class, 'updateFeedback']);
Route::get('/aspirasi/admin/{id}', [AspirasiController::class, 'getByAdmin']);
Route::get('/aspirasi/status/{status}', [AspirasiController::class, 'getByStatus']);
Route::apiResource('aspirasi', AspirasiController::class)->parameters(['aspirasi' => 'id']);

// Review routes (Admin Sistem)
Route::get('/review/pending', [ReviewController::class, 'pending']);
Route::patch('/review/{id}/accept', [ReviewController::class, 'accept']);
Route::patch('/review/{id}/reject', [ReviewController::class, 'reject']);
Route::get('/review/arsip', [ReviewController::class, 'arsip']);
Route::post('/review/auto-archive', [ReviewController::class, 'autoArchive']);

// Statistics routes
Route::get('/statistik/dashboard', [StatistikController::class, 'dashboard']);
Route::get('/statistik/trend', [StatistikController::class, 'trend']);
Route::get('/statistik/category', [StatistikController::class, 'categoryDistribution']);
Route::get('/statistik/status', [StatistikController::class, 'statusDistribution']);
Route::get('/statistik/summary', [StatistikController::class, 'summary']);

// Public Saran routes (no auth required for store)
Route::post('/saran-publik', [SaranPublikController::class, 'store']); // Public endpoint
Route::get('/saran-publik', [SaranPublikController::class, 'index']); // Admin only
Route::get('/saran-publik/statistics', [SaranPublikController::class, 'statistics']); // Admin stats
Route::get('/saran-publik/{id}', [SaranPublikController::class, 'show']);
Route::patch('/saran-publik/{id}/status', [SaranPublikController::class, 'updateStatus']);
Route::delete('/saran-publik/{id}', [SaranPublikController::class, 'destroy']);