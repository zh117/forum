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

Route::get('threads',                       'ThreadController@index')->name('threads');
Route::get('threads/create',                'ThreadController@create');
Route::get('threads/{channel}',             'ThreadController@index');
Route::get('threads/{channel}/{thread}',    'ThreadController@show');
Route::post('threads',                      'ThreadController@store')->middleware('must-be-confirmed');;
Route::delete('threads/{channel}/{thread}', 'ThreadController@destroy');
Route::patch('threads/{channel}/{thread}',  'ThreadController@update')->name('threads.update');

Route::get('/threads/{channel}/{thread}/replies',   'ReplyController@index');
Route::post('/threads/{channel}/{thread}/replies',  'ReplyController@store');
Route::patch('/replies/{reply}',                    'ReplyController@update');
Route::delete('/replies/{reply}',                   'ReplyController@destroy')->name('replies.destroy');

Route::post('/replies/{reply}/best','BestRepliesController@store')->name('best-replies.store');

Route::post('/replies/{reply}/favorites',   'FavoriteController@store');
Route::delete('/replies/{reply}/favorites', 'FavoriteController@destroy');

Route::get('/profiles/{user}','ProfilesController@show');
Route::get('/profiles/{user}','ProfilesController@show')->name('profile');

Route::post('/threads/{channel}/{thread}/subscriptions',    'ThreadSubscriptionsController@store')->middleware('auth');
Route::delete('/threads/{channel}/{thread}/subscriptions',  'ThreadSubscriptionsController@destroy')->middleware('auth');

Route::get('/profiles/{user}/notifications',                    'UserNotificationsController@index');
Route::delete('/profiles/{user}/notifications/{notification}',  'UserNotificationsController@destroy');

Route::get('api/users','Api\UsersController@index');

Route::post('api/users/{user}/avatar','Api\UserAvatarController@store')->middleware('auth')->name('avatar');

Route::get('/register/confirm','Auth\RegisterConfirmationController@index')->name('register.confirm');

Route::post('locked-threads/{thread}',  'LockedThreadsController@store')->name('locked-threads.store')->middleware('admin');
Route::delete('locked-threads/{thread}','LockedThreadsController@destroy')->name('locked-threads.destroy')->middleware('admin');
