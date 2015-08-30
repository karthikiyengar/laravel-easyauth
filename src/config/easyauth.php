<?php
/**
 * Created by PhpStorm.
 * User: karthik
 * Date: 8/29/15
 * Time: 11:31 AM
 */
return [
    'google' => [
        'clientId'      => '',
        'clientSecret'  => '',
        'redirectUri'   => 'http://localhost/callback',
        'scopes'        => ['PROFILE']
    ],
    'facebook' => [
        'clientId'          => '',
        'clientSecret'      => '',
        'redirectUri'       => 'http://localhost/callback',
        'graphApiVersion'   => 'v2.3'
    ],
    'email' => [
        'sender_email'      => 'us@paverblock.com',
        'sender_name'       => 'Paver Block',
        'forgot_password_subject' => 'Your password reset code',
        'email_activation_subject' => 'Confirm your account'
    ]
];