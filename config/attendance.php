<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Akun Admin Awal
    |--------------------------------------------------------------------------
    |
    | Dipakai oleh DatabaseSeeder untuk membuat/memperbarui akun admin.
    | Set ADMIN_EMAIL dan ADMIN_PASSWORD di environment (wajib di non-local).
    |
    */

    'admin' => [
        'name' => env('ADMIN_NAME', 'Administrator'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

];
