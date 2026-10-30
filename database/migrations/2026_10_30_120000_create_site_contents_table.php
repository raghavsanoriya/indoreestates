<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only edited website content is stored; untouched keys fall back to config/cms.php defaults.
        Schema::create('site_contents', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 150)->unique();
            $table->longText('value')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('site_content_revisions', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 150)->index();
            $table->longText('value')->nullable();
            $table->boolean('was_default')->default(false);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_content_revisions');
        Schema::dropIfExists('site_contents');
    }
};
