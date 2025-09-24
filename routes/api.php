<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhoneController;
use App\Http\Controllers\ClubController;

Route::post('phone/register-email', [PhoneController::class, 'registerEmail'])
    ->middleware('phone.key');

Route::post('phone/verify-email', [PhoneController::class, 'verifyEmail'])
    ->middleware('phone.key');

Route::post('/phone/registry-google', [PhoneController::class, 'registryGoogle'])
    ->middleware('phone.key');

Route::post('/clubs', [ClubController::class, 'store'])
    ->middleware('auth.phone');

Route::get('/my-clubs', [ClubController::class, 'myClubs'])
    ->middleware('auth.phone');
