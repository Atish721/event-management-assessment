<?php


use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1'); 

    
Route::middleware(['auth:sanctum', 'single.session'])->group(function () {
    Route::get('/check-auth', [AuthController::class, 'checkAuth']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    
    Route::get('/events', [EventController::class, 'index']);
    Route::post('/events', [EventController::class, 'store']);
    Route::delete('/events/{id}', [EventController::class, 'destroy']);
    
    
    Route::get('/admin/events', [EventController::class, 'adminList']);
    
    
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/nested', [CategoryController::class, 'nested']);
    Route::post('/categories', [CategoryController::class, 'store']);
});