<?php

use Illuminate\Http\Request;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\LaravelScheduledTasks1MinCron;


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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


/*
|--------------------------------------------------------------------------
| Laravel Scheduled Tasks 1 Minute Cron
|--------------------------------------------------------------------------
| This route should be called once per minute to establish the
| 'heartbeat' that Laravel requires to run Scheduled Tasks.
|--------------------------------------------------------------------------
*/

// laravel-scheduled-tasks-1min-cron
Route::get('/Api/v1/laravel-scheduled-tasks-1min-cron', [LaravelScheduledTasks1MinCron::class, 'runSchedule']);
// Route::get('/users', 'App\Http\Controllers\UserController@runSchedule');


/*
|--------------------------------------------------------------------------
| K2CacheApi
|--------------------------------------------------------------------------
*/

// k2cache/v1 
// Route::group(['prefix' => 'k2cache/v1', 'namespace' => 'Api\v1'], function () {
//   Route::get('test', [App\Http\Controllers\Api\v1\K2CacheApiController::class, 'index']);
// });


/*
|--------------------------------------------------------------------------
| HubSpotApi
|--------------------------------------------------------------------------
*/

// hubspot/v1
// Route::group(['prefix' => 'hubspot/v1', 'namespace' => 'Api\v1'], function () {
//   Route::get('test', [App\Http\Controllers\Api\v1\HubSpotApiController::class, 'index']);
// });
