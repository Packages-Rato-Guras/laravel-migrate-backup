<?php

namespace RatoGuras\LaravelMigrateBackup\Listeners;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class BackupDatabaseListener
{
    public function handle(CommandStarting $event)
    {
        if (! in_array($event->command->getName(), config('migrate-backup.commands', []))) {
            return;
        }

        $this->performBackup($event->command);
    }

    protected function performBackup($command)
    {
        $connectionName = config('database.default');
        $config = config("database.connections.{$connectionName}");

        $backupDir = base_path(config('migrate-backup.path'));

        if (! File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "{$timestamp}.sql";
        $backupPath = "{$backupDir}/{$filename}";

        $driver = $config['driver'];

        try {
            match ($driver) {
                'mysql', 'mariadb' => $this->backupMySQL($config, $backupPath),
                'pgsql' => $this->backupPostgreSQL($config, $backupPath),
                'sqlite' => $this->backupSQLite($config, $backupPath),
                default => throw new \Exception("Driver {$driver} not supported for auto-backup."),
            };

            $command->info("✅ Database backup created: database/backups/{$filename}");
        } catch (\Exception $e) {
            $command->error("❌ Backup failed: " . $e->getMessage());
        }
    }

    protected function backupMySQL($config, $path)
    {
        $cmd = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s 2>/dev/null',
            $config['host'],
            $config['port'] ?? 3306,
            $config['username'],
            escapeshellarg($config['password']),
            $config['database'],
            $path
        );

        shell_exec($cmd);
    }

    protected function backupPostgreSQL($config, $path)
    {
        putenv("PGPASSWORD=" . $config['password']);

        $cmd = sprintf(
            'pg_dump --host=%s --port=%s --username=%s --dbname=%s --no-owner --no-acl > %s 2>/dev/null',
            $config['host'],
            $config['port'] ?? 5432,
            $config['username'],
            $config['database'],
            $path
        );

        shell_exec($cmd);
        putenv('PGPASSWORD');
    }

    protected function backupSQLite($config, $path)
    {
        File::copy($config['database'], $path);
    }
}