<?php

return [
    'guard' => 'web',
    'password_timeout' => 10800,
    'username' => 'email',
    'email' => 'email',
    'prefix' => '',
    'views' => true,
    'features' => [
        \Laravel\Fortify\Features::registration(),
        \Laravel\Fortify\Features::emailVerification(),
        \Laravel\Fortify\Features::resetPasswords(),
        \Laravel\Fortify\Features::updateProfileInformation(),
        \Laravel\Fortify\Features::updatePasswords(),
        \Laravel\Fortify\Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]),
    ],
];