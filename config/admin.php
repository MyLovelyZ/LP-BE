<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Initial Administrator
    |--------------------------------------------------------------------------
    |
    | The account created by `php artisan db:seed`. Change the password from the
    | admin panel ("Akun") after the first sign-in, or create a different
    | administrator with `php artisan admin:create`.
    |
    */

    'name' => env('ADMIN_NAME', 'Administrator'),

    'email' => env('ADMIN_EMAIL', 'admin@pelitanusantara.sch.id'),

    'password' => env('ADMIN_PASSWORD', 'password'),

];
