<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix("auth")->group(function(){
    Route::post("register", array(AuthController::class, "register"));
    Route::post("login", array(AuthController::class, "login"));
    Route::post("save-question", array(AuthController::class, "saveQuestions"))->middleware(['auth:api']);
});
