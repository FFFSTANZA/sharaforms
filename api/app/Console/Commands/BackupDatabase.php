<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'app:backup-database';

    protected $description = 'Create a database backup and prune expired backup files';

    public function handle(): int
    {
        if (! config('backups.enabled')) {
            $this->info('Database backups are disabled.');

            return self::SUCCESS;
        }

        $connectionName = config('database.default');
        $connection = config('database.connections.' . $connectionName);

        if (! is_array($connection)) {
            $this->error('Active database connection configuration could not be resolved.');

            return self::FAILURE;
        }

        [$contents, $extension] = $this->dumpDatabase($connectionName, $connection);

        if ($contents === null || $extension === null) {
            return self::FAILURE;
        }

        $timestamp = now()->utc()->format('Ymd_His');
        $basePath = trim((string) config('backups.path', 'backups/database'), '/');
        $compress = (bool) config('backups.compress', true);
        $filename = sprintf('%s-%s-%s.%s', config('app.name', 'SharaForms'), $connectionName, $timestamp, $extension);

        if ($compress && $extension === 'sql') {
            $compressedContents = gzencode($contents, 9);

            if ($compressedContents === false) {
                $this->error('Unable to compress the generated SQL backup.');

                return self::FAILURE;
            }

            $contents = $compressedContents;
            $filename .= '.gz';
        }

        $disk = (string) config('backups.disk', 'local');
        $relativePath = $basePath . '/' . now()->utc()->format('Y/m') . '/' . $filename;

        Storage::disk($disk)->put($relativePath, $contents);
        $this->pruneExpiredBackups($disk, $basePath);

        $this->info('Database backup created at ' . $relativePath);

        return self::SUCCESS;
    }

    private function dumpDatabase(string $connectionName, array $connection): array
    {
        $driver = strtolower((string) ($connection['driver'] ?? ''));

        return match ($driver) {
            'pgsql' => $this->dumpPostgres($connectionName, $connection),
            'mysql', 'mariadb' => $this->dumpMySql($connectionName, $connection),
            'sqlite' => $this->dumpSqlite($connectionName, $connection),
            default => $this->unsupportedDriver($driver),
        };
    }

    private function dumpPostgres(string $connectionName, array $connection): array
    {
        $process = new Process([
            'pg_dump',
            '--no-owner',
            '--no-privileges',
            '--format=plain',
            '--host=' . (string) ($connection['host'] ?? '127.0.0.1'),
            '--port=' . (string) ($connection['port'] ?? '5432'),
            '--username=' . (string) ($connection['username'] ?? ''),
            '--dbname=' . (string) ($connection['database'] ?? ''),
        ]);

        if (! empty($connection['password'])) {
            $process->setEnv(['PGPASSWORD' => (string) $connection['password']]);
        }

        return $this->runDumpProcess($connectionName, $process);
    }

    private function dumpMySql(string $connectionName, array $connection): array
    {
        $command = [
            'mysqldump',
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--host=' . (string) ($connection['host'] ?? '127.0.0.1'),
            '--port=' . (string) ($connection['port'] ?? '3306'),
            '--user=' . (string) ($connection['username'] ?? ''),
            (string) ($connection['database'] ?? ''),
        ];

        if (! empty($connection['password'])) {
            $command[] = '--password=' . (string) $connection['password'];
        }

        $process = new Process($command);

        return $this->runDumpProcess($connectionName, $process);
    }

    private function dumpSqlite(string $connectionName, array $connection): array
    {
        $databasePath = (string) ($connection['database'] ?? '');

        if ($databasePath === '' || $databasePath === ':memory:' || ! is_file($databasePath)) {
            $this->error('Unable to back up the active sqlite database for [' . $connectionName . '].');

            return [null, null];
        }

        $contents = file_get_contents($databasePath);

        if ($contents === false) {
            $this->error('Failed to read the active sqlite database file for backup.');

            return [null, null];
        }

        return [$contents, 'sqlite'];
    }

    private function unsupportedDriver(string $driver): array
    {
        $this->error('Database backups are not implemented for the [' . $driver . '] driver.');

        return [null, null];
    }

    private function runDumpProcess(string $connectionName, Process $process): array
    {
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('Database backup failed for [' . $connectionName . ']: ' . trim($process->getErrorOutput()));

            return [null, null];
        }

        return [$process->getOutput(), 'sql'];
    }

    private function pruneExpiredBackups(string $disk, string $basePath): void
    {
        $retentionDays = max(1, (int) config('backups.retention_days', 14));
        $cutoffTimestamp = now()->subDays($retentionDays)->getTimestamp();

        foreach (Storage::disk($disk)->allFiles($basePath) as $path) {
            $lastModified = Storage::disk($disk)->lastModified($path);

            if ($lastModified === false) {
                continue;
            }

            if ($lastModified < $cutoffTimestamp) {
                Storage::disk($disk)->delete($path);
            }
        }
    }
}
