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
    Route::get('/image/{image}', 'ImageController@show')->name('image.show');
    Route::get('/category', 'CategoryController@index')->name('category');

    Route::prefix('/auth')->group(function () {
        Route::post('/register', 'AuthController@register');
        Route::post('/login', 'AuthController@login');
        // Route::post('/activation', 'AuthController@postActivation');
        // Route::get('/activation', 'AuthController@getActivation');
        // Route::post('/reset_password', 'AuthController@resetPassword');

        Route::get('/lesson', 'LessonController@authIndex');
        Route::get('/lesson/{slug}', 'LessonController@authShow');

        Route::get('/article', 'ArticleController@authIndex');
        Route::get('/article/{slug}', 'ArticleController@show');
    });

    Route::prefix('/guest')->middleware('auth:api')->group(function () {
        Route::get('/lesson', 'LessonController@guestIndex');
        Route::get('/lesson/{slug}', 'LessonController@guestShow');
        Route::post('/lesson', 'LessonController@store')->middleware('lesson:api');
        Route::post('/lesson/{slug}', 'LessonController@update')->middleware('lesson:api');
        Route::delete('/lesson/{slug}', 'LessonController@destroy')->middleware('lesson:api');

        Route::get('/lesson/{slug}/part', 'LessonPartController@guestIndex');
        Route::get('/lesson/{slug}/{slugPart}', 'LessonPartController@guestShow');
        Route::post('/lesson/{slug}/part', 'LessonPartController@store')->middleware('lesson:api');
        Route::put('/lesson/{slug}/{slugPart}', 'LessonPartController@update')->middleware('lesson:api');
        Route::delete('/lesson/{slug}/{slugPart}', 'LessonPartController@destroy')->middleware('lesson:api');

        Route::get('/article', 'ArticleController@guestIndex');
        Route::get('/article/{slug}', 'ArticleController@show');
        Route::post('/article', 'ArticleController@store')->middleware('article:api');
        Route::post('/article/{slug}', 'ArticleController@update')->middleware('article:api');
        Route::delete('/article/{slug}', 'ArticleController@destroy')->middleware('article:api');
    });
});
