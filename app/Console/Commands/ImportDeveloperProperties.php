<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\DeveloperProjectImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('properties:import-developer {source : ruchi or emerald} {--owner= : Existing agent or administrator email} {--publish : Publish the imported Indore projects} {--snapshot : Use the downloaded local source snapshot}')]
#[Description('Import Indore developer projects without duplicating records or overwriting local edits')]
class ImportDeveloperProperties extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(DeveloperProjectImporter $importer): int
    {
        $source = (string) $this->argument('source');
        $owner = User::where('email', (string) $this->option('owner'))->first();
        if (! isset(DeveloperProjectImporter::SOURCES[$source]) || ! $owner?->canList()) {
            $this->error('Choose ruchi or emerald and provide --owner with an existing agent or administrator email.');

            return self::FAILURE;
        }
        try {
            $snapshot = $this->option('snapshot') ? file_get_contents(storage_path('app/private/'.($source === 'ruchi' ? 'ruchi-realty' : 'emerald-developers').'-source.html')) : null;
            if ($snapshot === false) {
                throw new \RuntimeException('The source snapshot is missing.');
            }
            $result = $importer->import($source, $owner, (bool) $this->option('publish'), $snapshot, fn (string $message) => $this->line($message));
            $this->info('Created '.$result['created'].'; skipped '.$result['skipped'].'; failed '.count($result['errors']).'.');
            foreach ($result['errors'] as $error) {
                $this->error($error);
            }

            return $result['errors'] === [] ? self::SUCCESS : self::FAILURE;
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
