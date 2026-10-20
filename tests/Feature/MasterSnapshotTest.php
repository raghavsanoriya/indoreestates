<?php

namespace Tests\Feature;

use App\Console\Commands\TransferMasterSnapshot;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MasterSnapshotTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_export_preserves_ids_and_hashes_without_changing_source_records(): void
    {
        Storage::fake('local');
        $property = Property::factory()->create();
        $file = Storage::disk('local')->path('master.json');

        $this->artisan('estate:master-snapshot', ['action' => 'export', 'file' => $file])->assertSuccessful();

        $snapshot = json_decode(file_get_contents($file), true, 64, JSON_THROW_ON_ERROR);
        $this->assertSame($property->id, $snapshot['tables']['properties'][0]['id']);
        $this->assertSame(User::findOrFail($property->user_id)->password, $snapshot['tables']['users'][0]['password']);
        $this->assertDatabaseCount('properties', 1);
        $this->assertArrayNotHasKey('sessions', $snapshot['tables']);
        $this->assertArrayNotHasKey('password_reset_tokens', $snapshot['tables']);
    }

    public function test_import_preserves_property_ownership_and_unknown_prices(): void
    {
        Storage::fake('local');
        $owner = User::factory()->make(['id' => 41, 'role' => 'customer']);
        $property = Property::factory()->make(['id' => 81, 'user_id' => 41, 'price' => null, 'reference' => 'IE-MASTER-81', 'is_demo' => false]);
        $snapshot = $this->snapshot();
        $snapshot['tables']['users'] = [$this->row('users', $owner->getAttributes())];
        $snapshot['tables']['properties'] = [$this->row('properties', $property->getAttributes())];
        Storage::disk('local')->put('master.json', json_encode($snapshot, JSON_THROW_ON_ERROR));

        $this->artisan('estate:master-snapshot', ['action' => 'import', 'file' => Storage::disk('local')->path('master.json')])->assertSuccessful();

        $this->assertDatabaseHas('properties', ['id' => 81, 'user_id' => 41, 'price' => null]);
        $this->assertDatabaseHas('users', ['id' => 41, 'email' => $owner->email, 'password' => $owner->password]);
    }

    public function test_import_refuses_to_merge_into_existing_data(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        Storage::disk('local')->put('master.json', json_encode($this->snapshot(), JSON_THROW_ON_ERROR));

        $this->artisan('estate:master-snapshot', ['action' => 'import', 'file' => Storage::disk('local')->path('master.json')])->assertFailed();

        $this->assertModelExists($owner);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_invalid_columns_are_rejected_before_any_rows_are_written(): void
    {
        Storage::fake('local');
        $snapshot = $this->snapshot();
        $snapshot['tables']['users'] = [['id' => 2, 'unexpected' => 'not allowed']];
        Storage::disk('local')->put('master.json', json_encode($snapshot, JSON_THROW_ON_ERROR));

        $this->artisan('estate:master-snapshot', ['action' => 'import', 'file' => Storage::disk('local')->path('master.json')])->assertFailed();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_verification_creates_completion_marker_only_after_matching_data_and_photos(): void
    {
        Storage::fake('local');
        $property = Property::factory()->create();
        Storage::disk('local')->put('property-photos/imported.jpg', 'image fixture');
        $property->photos()->create(['path' => 'property-photos/imported.jpg', 'position' => 0]);
        $file = Storage::disk('local')->path('master.json');
        $this->artisan('estate:master-snapshot', ['action' => 'export', 'file' => $file])->assertSuccessful();

        $this->artisan('estate:master-snapshot', ['action' => 'verify', 'file' => $file])->assertSuccessful();

        $this->assertSame(hash_file('sha256', $file), file_get_contents(dirname($file).'/.master-import-complete'));
    }

    public function test_missing_media_prevents_the_completion_marker(): void
    {
        Storage::fake('local');
        $property = Property::factory()->create();
        $property->photos()->create(['path' => 'property-photos/missing.jpg', 'position' => 0]);
        $file = Storage::disk('local')->path('master.json');
        $this->artisan('estate:master-snapshot', ['action' => 'export', 'file' => $file])->assertSuccessful();

        $this->artisan('estate:master-snapshot', ['action' => 'verify', 'file' => $file])->assertFailed();

        Storage::disk('local')->assertMissing('.master-import-complete');
    }

    private function snapshot(): array
    {
        $snapshot = ['format' => 1, 'tables' => [], 'columns' => []];
        foreach (TransferMasterSnapshot::TABLES as $table) {
            $snapshot['tables'][$table] = [];
            $snapshot['columns'][$table] = DB::connection()->getSchemaBuilder()->getColumnListing($table);
        }

        return $snapshot;
    }

    private function row(string $table, array $attributes): array
    {
        $columns = DB::connection()->getSchemaBuilder()->getColumnListing($table);

        return array_replace(array_fill_keys($columns, null), $attributes);
    }
}
