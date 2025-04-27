<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get("/", [UserController::class, "index"])->name("index");
Route::post("/register", [UserController::class, "register"])->name("register");
Route::get('/departments', [UserController::class, 'getDepartments']);
Route::get('/cities', [UserController::class, 'getCities']);
Route::get('/users', [UserController::class, 'list']);
Route::post('/updateWin', [UserController::class, 'updateWin'])->name("updateWin");
Route::get('/listUsers', [UserController::class, 'listUsers'])->name("listUsers");

