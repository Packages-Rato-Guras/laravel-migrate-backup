<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backup Path
    |--------------------------------------------------------------------------
    |
    | Where to store the backups (relative to project root).
    |
    */
    'path' => 'database/backups',

    /*
    |--------------------------------------------------------------------------
    | Commands that trigger backup
    |--------------------------------------------------------------------------
    |
    | Add any Artisan commands here that should trigger a backup before running.
    |
    */
    'commands' => [
        'migrate:fresh',
        'migrate:refresh',
        // 'migrate:reset', // uncomment if you want
    ],

];