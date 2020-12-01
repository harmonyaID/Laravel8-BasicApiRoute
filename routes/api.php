<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SysRestoController;
use App\Http\Controllers\SysOrderController;
use App\Http\Controllers\SysCustomerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'v1'], function(){

    // GET Authentication 
    Route::post('customers/login', [SysCustomerController::class, 'login']);
    Route::post('restaurants/login', [SysRestoController::class, 'login']);

    // GET DATA 
    Route::get('customers/orders', [SysOrderController::class, 'orderByUsers']);
    Route::get('customers/top-orders', [SysOrderController::class, 'getTopUsersTransaction']);
    Route::get('restaurants/open-certain-times', [SysRestoController::class, 'getRestoOpenCertainTime']);
    Route::get('restaurants/number-dishes', [SysRestoController::class, 'getRestoCertainMenu']);
    Route::get('restaurants/orders', [SysOrderController::class, 'orderByResto']);
    Route::get('restaurants/locations', [SysRestoController::class, 'getRestoByLocationUser']);
    Route::get('restaurants/populars', [SysRestoController::class, 'getRestoPopular']);
    Route::get('restaurants/search-dishes', [SysRestoController::class, 'getSearchData']);
    Route::get('orders/customers-total', [SysOrderController::class, 'totalUsersOrder']);


});