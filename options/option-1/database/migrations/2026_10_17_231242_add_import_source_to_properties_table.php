<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table): void {
            $table->string('source_key')->nullable()->unique();
            $table->string('source_url', 1000)->nullable();
            $table->json('source_details')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->decimal('price', 14, 2)->nullable()->change();
            $table->decimal('area', 12, 2)->nullable()->change();
            foreach (['rooms', 'bedrooms', 'bathrooms', 'garages'] as $field) {
                $table->unsignedSmallInteger($field)->nullable()->default(null)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table): void {
            $table->dropUnique(['source_key']);
            $table->dropColumn(['source_key', 'source_url', 'source_details', 'imported_at']);
        });
        // Retain nullability so a rollback does not invent unknown measurements.
    }
};
