<?php

use Laravel\Fortify\Features;

return [

    'guard' => 'web',

    'passwords' => 'users',

    'username' => 'email',

    'email' => 'email',

    'lowercase_usernames' => true,

    // ✅ Redirect after login
    'home' => '/admin/dashboard',

    // ✅ Fortify redirect map (optional but fine)
    'redirects' => [
        'login' => '/admin/dashboard',
    ],

    // ✅ IMPORTANT: make Fortify login POST = /admin/login
    'prefix' => 'admin',

    'domain' => null,

    'middleware' => ['web'],

    'limiters' => [
        'login' => 'login',
        'two-factor' => 'two-factor',
    ],

    // ✅ since you already made your own Inertia login page route (GET /admin/login)
    // disable Fortify view routes to avoid conflicts
    'views' => false,

    'features' => [
        Features::registration(),
        Features::resetPasswords(),
        Features::emailVerification(),
        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]),
    ],

];
