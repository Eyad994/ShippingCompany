<?php

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');
    Route::group([
        'middleware' => 'auth:api'
    ], function () {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
    });
});

Route::get('/allAddresses', 'HomeController@allAddresses');

Route::get('/allAddresses/{status}', 'HomeController@allAddressWithStatus');

Route::post('/address', 'HomeController@address');

Route::get('/delete/{id}', 'HomeController@destroy');
