<?php

namespace RatoGuras\LaravelMigrateBackup\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Events\CommandStarting;
use RatoGuras\LaravelMigrateBackup\Listeners\BackupDatabaseListener;

class LaravelMigrateBackupServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__ . '/../../config/migrate-backup.php' => config_path('migrate-backup.php'),
            ], 'migrate-backup-config');

            // Register the listener
            $this->app['events']->listen(
                CommandStarting::class,
                BackupDatabaseListener::class
            );
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/migrate-backup.php',
            'migrate-backup'
        );
    }
}