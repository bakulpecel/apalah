<?php

use Illuminate\Http\Request;

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

Route::namespace('Api')->group(function () {
    Route::prefix('/auth')->group(function () {
        Route::post('/register', 'AuthController@register');
        Route::post('/login', 'AuthController@login');
        // Route::post('/activation', 'AuthController@postActivation');
        // Route::get('/activation', 'AuthController@getActivation');
        // Route::post('/reset_password', 'AuthController@resetPassword');
    });

    Route::prefix('/lesson')->group(function () {
        Route::get('', 'LessonController@index');
        Route::get('/publish', 'LessonController@indexPublish');
        Route::get('/{slug}', 'LessonController@show');
        
        Route::middleware(['auth:api', 'lesson:api'])->group(function () {
            Route::post('', 'LessonController@store');
            // Route::put('/{slug}', 'LessonController@update');
            Route::delete('/{slug}', 'LessonController@destroy');

            Route::post('/{slug}', 'LessonPartController@store');
        });
    });
});
