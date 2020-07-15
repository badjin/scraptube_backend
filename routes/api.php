<?php

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

Route::post('/register','Api\AuthController@register');
Route::post('/login','Api\AuthController@login');

Route::post('/password/forgot','Api\ForgotPasswordController@sendResetLinkEmail');
Route::post('/password/reset','Api\ResetPasswordController@reset');

Route::get('/email/resend', 'Api\VerificationController@resend')->name('verification.resend');
Route::get('/email-verification', 'Api\VerificationController@verify')->name('verification.verify');

Route::middleware('auth:api')->group(function () {
    Route::post('/logout','Api\AuthController@logout');
    Route::get('/user','Api\AuthController@getUser');
    Route::post('/update-user','Api\AuthController@updateUser');
    Route::post('/update-thumbUp','Api\AuthController@updateThumbUpPlaylist');
    Route::post('/update-member','Api\AuthController@update');
    Route::post('/change-password','Api\AuthController@changePassword');
    Route::post('/delete-member','Api\AuthController@destroy');
    Route::get('/admin/users','Api\AuthController@getUsers');
});

Route::get('admin/notice/index', 'NoticeController@index');
Route::prefix('admin/notice')->middleware('auth:api')->group(function (){
    Route::post('/store', 'NoticeController@store');
    Route::post('/delete', 'NoticeController@destroy');
});

Route::prefix('scrapbook')->middleware('auth:api')->group(function () {
    Route::get('/index', 'ScrapbookController@index');
    Route::post('/store', 'ScrapbookController@store');
    Route::post('/update', 'ScrapbookController@update');
    Route::post('/delete', 'ScrapbookController@destroy');
});

Route::prefix('category')->middleware('auth:api')->group(function () {
    Route::get('/index', 'CategoryController@index');
    Route::post('/store', 'CategoryController@store');
});

Route::prefix('tag-items')->middleware('auth:api')->group(function () {
    Route::get('/index', 'TagItemsController@index');
    Route::post('/store', 'TagItemsController@store');
    Route::post('/update', 'TagItemsController@update');
    Route::post('/delete', 'TagItemsController@destroy');
});

Route::get('playlist/index', 'PlaylistController@index');
Route::prefix('playlist')->middleware('auth:api')->group(function () {
    Route::post('/store', 'PlaylistController@store');
    Route::post('/update', 'PlaylistController@update');
    Route::post('/updateThumbUp', 'PlaylistController@updateThumbUp');
    Route::post('/delete', 'PlaylistController@destroy');
});

