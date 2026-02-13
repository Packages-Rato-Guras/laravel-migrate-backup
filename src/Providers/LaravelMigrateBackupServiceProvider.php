<?php

namespace RatoGuras\LaravelMigrateBackup\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Events\CommandStarting;
use RatoGuras\LaravelMigrateBackup\Listeners\BackupDatabaseListener;
use Illuminate\Contracts\Foundation\Application;

/**
 * @property Application $app
 */
class LaravelMigrateBackupServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__.'/../../config/migrate-backup.php'
                    => $this->app->configPath('migrate-backup.php'),
            ], 'migrate-backup-config');

            $this->app['events']->listen(
                CommandStarting::class,
                BackupDatabaseListener::class
            );
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/migrate-backup.php',
            'migrate-backup'
        );
    }
}
