<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserRankingController;

Route::group(['middleware' => ['api']], function(){
  Route::post('/user/ranking/add', [UserRankingController::class, 'add_user_ranking']);
  Route::post('/user/ranking/get', [UserRankingController::class, 'get_user_ranking']);
});
