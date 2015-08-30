<?php
/**
 * Created by PhpStorm.
 * User: karthik
 * Date: 8/23/15
 * Time: 11:24 PM
 */

    Route::group(['prefix' => 'auth'], function () {

    Route::post('/register', 'Paverblock\Easyauth\Controllers\RegistrationController@registerUser');
    Route::get('/register/confirm', 'Paverblock\Easyauth\Controllers\RegistrationController@confirmRegistration');

    Route::post('/login/web', 'Paverblock\Easyauth\Controllers\AuthenticationController@authenticate');
    Route::post('/login/google', 'Paverblock\Easyauth\Controllers\AuthenticationController@authenticateGoogle');
    Route::post('/login/facebook', 'Paverblock\Easyauth\Controllers\AuthenticationController@authenticateFacebook');
    Route::post('/logout', 'Paverblock\Easyauth\Controllers\AuthenticationController@logout');

    Route::post('/forgot-password', 'Paverblock\Easyauth\Controllers\PasswordController@sendForgotPasswordEmail');
    Route::post('/password-reset', 'Paverblock\Easyauth\Controllers\PasswordController@passwordReset');
    Route::get('/password-reset', 'Paverblock\Easyauth\Controllers\PasswordController@getPasswordResetForm');

    /***
     * Routes that require a registered user (Token in header)
     */
    Route::group(['middleware' => 'jwt.auth'], function() {
        Route::post('/change-password', 'Paverblock\Easyauth\Controllers\PasswordController@getPasswordResetForm');
        Route::post('/deactivate', 'Paverblock\Easyauth\Controllers\RegistrationController@deactivateUser');
    });
});
