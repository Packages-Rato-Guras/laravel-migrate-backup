<?php

namespace RatoGuras\LaravelMigrateBackup\Listeners;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;

class BackupDatabaseListener
{
    /**
     * Handle the console command starting event.
     */
    public function handle(CommandStarting $event): void
    {
        /**
         * Laravel compatibility:
         * command may be string OR object
         */
        $commandName = is_string($event->command)
            ? $event->command
            : $event->command?->getName();

        // Commands to watch
        $watchCommands = Config::get(
            'migrate-backup.commands',
            ['migrate:fresh', 'migrate:refresh']
        );

        if (! in_array($commandName, $watchCommands)) {
            return;
        }

        $this->performBackup($event);
    }

    /**
     * Perform database backup.
     */
    protected function performBackup(CommandStarting $event): void
    {
        $connectionName = Config::get('database.default');

        $dbConfig = Config::get(
            "database.connections.{$connectionName}"
        );

        if (!$dbConfig) {
            $this->writeConsole($event, "❌ Database config not found.", true);
            return;
        }

        $app = app(Application::class);

        $backupDir = $app->basePath(
            Config::get('migrate-backup.path', 'database/backups')
        );


        if (! File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        // File name
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $database = $dbConfig['database'] ?? 'database';
        $filename = "{$database}_{$timestamp}.sql";
        $backupPath = "{$backupDir}/{$filename}";

        $driver = $dbConfig['driver'] ?? null;

        try {

            match ($driver) {
                'mysql', 'mariadb'
                    => $this->backupMySQL($dbConfig, $backupPath),

                'pgsql'
                    => $this->backupPostgreSQL($dbConfig, $backupPath),

                'sqlite'
                    => $this->backupSQLite($dbConfig, $backupPath),

                default
                    => throw new \Exception(
                        "Driver '{$driver}' not supported."
                    ),
            };

            $this->writeConsole(
                $event,
                "✅ Database backup created: {$filename}"
            );

        } catch (\Throwable $e) {

            $this->writeConsole(
                $event,
                "❌ Backup failed: ".$e->getMessage(),
                true
            );
        }
    }

    /**
     * MySQL / MariaDB backup.
     */
    protected function backupMySQL(array $config, string $path): void
    {
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 3306;
        $username = $config['username'] ?? 'root';
        $password = $config['password'] ?? '';
        $database = $config['database'];

        $command = sprintf(
            'MYSQL_PWD=%s mysqldump --host=%s --port=%s --user=%s %s > %s 2>/dev/null',
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($path)
        );

        shell_exec($command);
    }

    /**
     * PostgreSQL backup.
     */
    protected function backupPostgreSQL(array $config, string $path): void
    {
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 5432;
        $username = $config['username'];
        $password = $config['password'] ?? '';
        $database = $config['database'];

        putenv("PGPASSWORD={$password}");

        $command = sprintf(
            'pg_dump --host=%s --port=%s --username=%s --dbname=%s --no-owner --no-acl > %s 2>/dev/null',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($path)
        );

        shell_exec($command);

        putenv('PGPASSWORD');
    }

    /**
     * SQLite backup.
     */
    protected function backupSQLite(array $config, string $path): void
    {
        $databasePath = $config['database'];

        if (! File::exists($databasePath)) {
            throw new \Exception("SQLite database file not found.");
        }

        File::copy($databasePath, $path);
    }

    /**
     * Safe console output.
     */
    protected function writeConsole(
        CommandStarting $event,
        string $message,
        bool $error = false
    ): void {

        if (!is_string($event->command) && $event->command) {

            $error
                ? $event->command->error($message)
                : $event->command->info($message);
        }
    }
}
