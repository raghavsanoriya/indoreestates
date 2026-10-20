<?php

namespace App\Console\Commands;

use App\Services\DemoContentCleaner;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Signature('site:clean-demo {--apply : Back up and remove the selected records} {--property=* : Additional verified QA property IDs; sourced projects are always protected}')]
#[Description('Preview or remove seeded property content without touching sourced projects or user accounts')]
class CleanDemoContent extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(DemoContentCleaner $cleaner): int
    {
        $values = $this->option('property');
        foreach ($values as $value) {
            if (! ctype_digit((string) $value) || (int) $value < 1) {
                $this->error('Property IDs must be positive integers.');

                return self::FAILURE;
            }
        }
        $ids = array_map('intval', $values);
        $records = $cleaner->candidates($ids)->get(['id', 'title']);
        $this->table(['ID', 'Listing'], $records->map(fn ($property) => [$property->id, $property->title])->all());
        if (! $this->option('apply')) {
            $this->info('Preview only: '.$records->count().' listings selected. No data changed.');

            return self::SUCCESS;
        }
        if ($records->isEmpty()) {
            $this->info('No sample listings remain.');

            return self::SUCCESS;
        }
        $connection = DB::connection();
        $database = $connection->getDatabaseName();
        if ($connection->getDriverName() !== 'sqlite' || ! is_file($database)) {
            $this->error('Cleanup requires a file-backed SQLite database so a recoverable backup can be made.');

            return self::FAILURE;
        }
        try {
            $backupPath = Storage::disk('local')->path('pre-demo-cleanup-'.now()->format('Ymd-His').'-'.Str::ulid().'.sqlite');
            $source = new \SQLite3($database, SQLITE3_OPEN_READONLY);
            $backup = new \SQLite3($backupPath);
            $success = $source->backup($backup);
            $backup->close();
            $source->close();
            if (! $success) {
                throw new \RuntimeException('The database backup failed; no records were removed.');
            }
            $this->info('Backup: '.$backupPath);
            $this->info('Removed '.$cleaner->clean($ids).' sample/QA listings and their related activity.');
            $this->line('Accounts, sourced projects, contact submissions and subscriptions were preserved. Uploaded files are retained for recovery.');

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
