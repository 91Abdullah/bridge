<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

//Route::get('/home', 'HomeController@index')->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/report', 'ReportController@index')->name('reports');
    Route::get('/getData', 'ReportController@getData')->name('get.data');
    Route::get('/getFile/{fileId}', 'ReportController@getFile')->name('get.file');
    Route::get('/test', 'ReportController@test');
    Route::resource('/pinCodes', 'PinCodeController');
});
