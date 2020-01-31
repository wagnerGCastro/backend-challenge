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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post('auth/login', 'Api\AuthController@login')->name('auth.login');

Route::group(['middleware' => ['apiJwt']], function() {
    Route::post('auth/logout', 'Api\AuthController@logout')->name('auth.logout');

    // User
    Route::get('v1/user', 'Api\UserController@index')->name('user.index');

    // Product
    Route::get('v1/product', 'Api\ProductController@index')->name('product.index');
    Route::post('v1/product', 'Api\ProductController@store')->name('product.store');
    Route::get('v1/product/{id}', 'Api\ProductController@show')->name('product.show');
    Route::put('v1/product/{id}', 'Api\ProductController@update')->name('product.update');
    Route::delete('v1/product/{id}', 'Api\ProductController@destroy')->name('product.destroy');

});