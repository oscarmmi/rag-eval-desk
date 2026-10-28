<?php

use App\Http\Controllers\EvalRunController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EvalRunController::class, 'index'])->name('home');
Route::post('/eval-runs', [EvalRunController::class, 'store'])->name('eval-runs.store');
