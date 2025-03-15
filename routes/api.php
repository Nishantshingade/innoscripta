<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NewsController;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/login',[AuthController::class,'login'])->middleware('throttle:5,1');
Route::post('/register',[AuthController::class,'register'])->middleware('throttle:5,1');
Route::post('/reset',[AuthController::class,'reset'])->middleware('throttle:5,1');

Route::middleware(['auth:sanctum','throttle:5,1'])->group(function(){
    Route::post('/logout',[AuthController::class,'logout']);
    Route::post('/getArticles',[NewsController::class,'getArticles'])->name('getAll');
    Route::get('/article/{article}',[NewsController::class,'fetchArticle']);
    Route::get('/articles/list',[NewsController::class,'list']);
    Route::post('/preference/set',[NewsController::class,'setpreference'])->name('setpreference');
    Route::get('/preference/get/{userId}',[NewsController::class,'getpreference'])->name('getpreference');
});



