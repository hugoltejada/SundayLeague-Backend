<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhoneController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\MatchController;

Route::post('/phone/registry-email', [PhoneController::class, 'registryEmail'])
    ->middleware('firebase.auth');

Route::post('/phone/login-email', [PhoneController::class, 'loginEmail'])
    ->middleware('firebase.auth');

Route::post('/phone/registry-google', [PhoneController::class, 'registryGoogle'])
    ->middleware('phone.key');

Route::post('/clubs', [ClubController::class, 'store'])
    ->middleware('auth.phone');

Route::get('/my-clubs', [ClubController::class, 'myClubs'])
    ->middleware('auth.phone');

Route::post('/upload', [UploadController::class, 'store'])
    ->middleware('auth.phone');

Route::post('/clubs/{club}/schedule', [ClubController::class, 'updateSchedule'])
    ->middleware('auth.phone');

Route::post('/clubs/join', [ClubController::class, 'requestJoin'])
    ->middleware('auth.phone');

Route::post('/player/avatar', [PlayerController::class, 'updateAvatar'])
    ->middleware('auth.phone');

Route::post('/seasons', [SeasonController::class, 'store'])
    ->middleware('auth.phone');

Route::post('/matches', [MatchController::class, 'store'])
    ->middleware('auth.phone');
