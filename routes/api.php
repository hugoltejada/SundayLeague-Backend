<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhoneController;

Route::post('phone/register-email', [PhoneController::class, 'registerEmail'])
    ->middleware('phone.key');

Route::post('phone/verify-email', [PhoneController::class, 'verifyEmail'])
    ->middleware('phone.key');

Route::post('/phone/registry-google', [PhoneController::class, 'registryGoogle'])
    ->middleware('phone.key');
