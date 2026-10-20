<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class TransferMasterSnapshot extends Command
{
    protected $signature = 'estate:master-snapshot {action : export, import or verify} {file : Private JSON snapshot path}';

    protected $description = 'Transfer Option 3 master records to an empty real-estate database without resetting data';

    public const TABLES = ['users', 'amenities', 'properties', 'property_photos', 'amenity_property', 'favorites', 'conversations', 'messages', 'reviews', 'property_views', 'contact_submissions', 'subscribers'];

    public function handle(): int
    {
        $file = (string) $this->argument('file');
        $action = (string) $this->argument('action');
        try {
            if (! in_array($action, ['export', 'import', 'verify'], true)) {
                throw new RuntimeException('Invalid action.');
            }
            if (! app()->isLocal() && ! app()->runningUnitTests()) {
                if ($action === 'export' || config('database.default') !== 'mysql' || config('database.connections.mysql.database') !== 'geicic3c_indore_estates') {
                    throw new RuntimeException('Production transfers require the dedicated real-estate database.');
                }
            }
            $parent = realpath(dirname($file));
            if ($parent === false || str_starts_with(str_replace('\\', '/', $parent).'/', str_replace('\\', '/', public_path()).'/') || is_link($file)) {
                throw new RuntimeException('Use a private directory outside the web root.');
            }
            if ($action === 'export') {
                if (file_exists($file)) {
                    throw new RuntimeException('Refusing to overwrite a snapshot.');
                }
                $snapshot = ['format' => 1, 'tables' => [], 'columns' => []];
                DB::transaction(function () use (&$snapshot): void {
                    foreach (self::TABLES as $table) {
                        $snapshot['columns'][$table] = Schema::getColumnListing($table);
                        $snapshot['tables'][$table] = DB::table($table)->get()->map(fn (object $row): array => (array) $row)->all();
                    }
                });
                $handle = fopen($file, 'x');
                if ($handle === false) {
                    throw new RuntimeException('Cannot create snapshot.');
                }
                try {
                    chmod($file, 0600);
                    $json = json_encode($snapshot, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                    if (fwrite($handle, $json) !== strlen($json)) {
                        throw new RuntimeException('Incomplete snapshot write.');
                    }
                } finally {
                    fclose($handle);
                }
                $this->info('Master snapshot exported privately; no records were changed.');

                return self::SUCCESS;
            }
            if (! is_file($file) || filesize($file) > 20 * 1024 * 1024) {
                throw new RuntimeException('Invalid snapshot file.');
            }
            $snapshot = json_decode(file_get_contents($file), true, 64, JSON_THROW_ON_ERROR);
            if (($snapshot['format'] ?? null) !== 1 || array_keys($snapshot['tables'] ?? []) !== self::TABLES) {
                throw new RuntimeException('Invalid snapshot format.');
            }
            foreach (self::TABLES as $table) {
                $columns = Schema::getColumnListing($table);
                $sourceColumns = $snapshot['columns'][$table] ?? [];
                sort($columns);
                sort($sourceColumns);
                if ($columns !== $sourceColumns || ! is_array($snapshot['tables'][$table])) {
                    throw new RuntimeException('Snapshot schema mismatch.');
                }
                foreach ($snapshot['tables'][$table] as $row) {
                    $rowColumns = is_array($row) ? array_keys($row) : [];
                    sort($rowColumns);
                    if ($columns !== $rowColumns) {
                        throw new RuntimeException('Invalid snapshot columns.');
                    }
                }
            }
            if ($action === 'verify') {
                foreach (self::TABLES as $table) {
                    if (DB::table($table)->count() !== count($snapshot['tables'][$table])) {
                        throw new RuntimeException('Imported row counts do not match.');
                    }
                }
                foreach (DB::table('property_photos')->get() as $photo) {
                    if ($photo->is_demo || ! str_starts_with($photo->path, 'property-photos/') || ! Storage::disk('local')->exists($photo->path)) {
                        throw new RuntimeException('Imported media is missing.');
                    }
                }
                if (file_put_contents(dirname($file).'/.master-import-complete', hash_file('sha256', $file), LOCK_EX) === false) {
                    throw new RuntimeException('Cannot write verified import marker.');
                }
                $this->info('Master counts and listing photos verified.');

                return self::SUCCESS;
            }
            DB::transaction(function () use ($snapshot): void {
                foreach (self::TABLES as $table) {
                    if (DB::table($table)->exists()) {
                        throw new RuntimeException('Target database must be empty.');
                    }
                }
                foreach (self::TABLES as $table) {
                    foreach (array_chunk($snapshot['tables'][$table], 100) as $rows) {
                        DB::table($table)->insert($rows);
                    }
                }
            });
            $this->info('Master records imported with original IDs and ownership.');

            return self::SUCCESS;
        } catch (Throwable) {
            $this->error('Master transfer refused or failed. Check the private snapshot, matching schema, database restriction and empty target; existing records were not reset.');

            return self::FAILURE;
        }
    }
}
