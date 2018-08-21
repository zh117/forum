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

Route::get('/home', 'HomeController@index')->name('home');

Route::get('threads',                       'ThreadController@index');
Route::get('threads/create',                'ThreadController@create');
Route::get('threads/{channel}',             'ThreadController@index');
Route::get('threads/{channel}/{thread}',    'ThreadController@show');
Route::post('threads',                      'ThreadController@store');
Route::delete('threads/{channel}/{thread}', 'ThreadController@destroy');

Route::get('/threads/{channel}/{thread}/replies',   'ReplyController@index');
Route::post('/threads/{channel}/{thread}/replies',  'ReplyController@store');
Route::patch('/replies/{reply}',                    'ReplyController@update');
Route::delete('/replies/{reply}',                   'ReplyController@destroy');

Route::post('/replies/{reply}/favorites',   'FavoriteController@store');
Route::delete('/replies/{reply}/favorites', 'FavoriteController@destroy');

Route::get('/profiles/{user}','ProfilesController@show');
Route::get('/profiles/{user}','ProfilesController@show')->name('profile');

Route::post('/threads/{channel}/{thread}/subscriptions',    'ThreadSubscriptionsController@store')->middleware('auth');
Route::delete('/threads/{channel}/{thread}/subscriptions',  'ThreadSubscriptionsController@destroy')->middleware('auth');

Route::get('/profiles/{user}/notifications',                    'UserNotificationsController@index');
Route::delete('/profiles/{user}/notifications/{notification}',  'UserNotificationsController@destroy');

Route::get('api/users','Api\UsersController@index');
