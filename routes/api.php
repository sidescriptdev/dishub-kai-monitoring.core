<?php

use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\Information\InformationController;
use App\Http\Controllers\Api\Monitoring\CategoryController;
use App\Http\Controllers\Api\Monitoring\ImageController;
use App\Http\Controllers\Api\Monitoring\InputController;
use App\Http\Controllers\Api\Monitoring\InputValueController;
use App\Http\Controllers\Api\Monitoring\MonitoringController;
use App\Http\Controllers\Api\Monitoring\ObjectDataController;
use App\Http\Controllers\Api\Monitoring\OptionController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\UserController;
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

Route::name('api.')->group(function() {
    Route::post('login', [UserController::class, 'login']);
    Route::post('verification', [UserController::class, 'verification']);
    Route::resource('/employee', EmployeeController::class);

    Route::middleware('auth:sanctum')->group(function() {
        Route::resource('/category-monitoring', CategoryController::class);
        Route::resource('/object', ObjectDataController::class);
        Route::resource('/monitoring', MonitoringController::class);
        Route::resource('/input-monitoring', InputController::class);
        Route::delete('/input-monitoring/delete-image/{id}', [InputController::class, 'deleteImage'])->name('api.input-monitoring.delete-image');
        Route::resource('/option-input-monitoring', OptionController::class);
        Route::resource('/image-monitoring', ImageController::class);
        Route::resource('/user', UserController::class);
        Route::post('/input-value', [InputValueController::class, 'store'])->name('value.store');
        Route::resource('/team', TeamController::class);
        Route::resource('/information', InformationController::class);
        Route::post('/information/image/{id}/delete', [InformationController::class, 'deleteImage']);
        Route::post('/team/{id}/add/employee', [TeamController::class, 'addEmployee'])->name('team.add.employee');
        Route::post('/team/{id}/remove/employee', [TeamController::class, 'removeEmployee'])->name('team.remove.employee');
        Route::post('/category-monitoring/{id}/add/object', [CategoryController::class, 'addObject']);
        Route::post('/category-monitoring/{id}/remove/object', [CategoryController::class, 'removeObject']);
    });
});
