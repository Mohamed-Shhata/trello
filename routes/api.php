<?php

use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Middleware\IsAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(['prefix' => 'v1', 'namespace' => 'App\Http\Controllers\Api\V1'], function () {
    //user authintication
    Route::post('/auth/register', [AuthController::class, 'createUser']);
    Route::post('/auth/login', [AuthController::class, 'loginUser'])->name('login');
    Route::get('/auth/logout', [AuthController::class, 'logoutUser'])->name('logout')->middleware('auth:sanctum');

    Route::post('/auth/addEmployee', [AuthController::class, 'addEmployee'])->middleware('auth:sanctum');

    //projects crud
    Route::apiResource('projects', ProjectController::class)->middleware(['auth:sanctum', 'isAdmin']);

    //tasks crud

    Route::apiResource('tasks', TaskController::class, [
        'only' => ['index', 'show']
    ])->middleware('auth:sanctum');
    Route::apiResource('tasks', TaskController::class, [
        'only' => ['store', 'update', 'destroy']
    ])->middleware(['auth:sanctum', 'isAdmin']);


    Route::get('projectTasks', [TaskController::class, 'projectTasks'])->middleware('auth:sanctum');
    Route::post('assignEmployee', [TaskController::class, 'assignEmployee'])->middleware(['auth:sanctum', 'isAdmin']);;
    Route::post('completeTask', [TaskController::class, 'completeTask'])->middleware('auth:sanctum');
});
