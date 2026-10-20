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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('customer');
            foreach (['phone', 'company', 'position', 'office_address', 'avatar_path'] as $field) {
                $table->string($field)->nullable();
            }
            $table->text('bio')->nullable();
            $table->json('social_links')->nullable();
        });
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('reference')->unique();
            $table->unsignedInteger('legacy_id')->nullable()->unique();
            $table->string('title');
            $table->text('description');
            $table->string('address');
            $table->string('location');
            $table->string('state')->nullable();
            $table->string('country')->default('India');
            $table->string('zip_code')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('type');
            $table->string('purpose');
            $table->decimal('price', 14, 2);
            $table->string('rental_period')->nullable();
            $table->decimal('area', 12, 2);
            $table->decimal('land_area', 12, 2)->nullable();
            foreach (['rooms', 'bedrooms', 'bathrooms', 'garages'] as $field) {
                $table->unsignedSmallInteger($field)->default(0);
            }
            $table->decimal('garage_area', 10, 2)->nullable();
            $table->unsignedSmallInteger('year_built')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('status')->default('draft');
            $table->string('availability')->default('available');
            $table->text('rejection_reason')->nullable();
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'availability', 'created_at']);
            $table->index(['user_id', 'status']);
        });
        Schema::create('property_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
        });
        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
        Schema::create('amenity_property', function (Blueprint $table) {
            $table->foreignId('amenity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->primary(['amenity_id', 'property_id']);
        });
        Schema::create('favorites', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['user_id', 'property_id']);
        });
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('agent_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['property_id', 'customer_id']);
        });
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('body');
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->unique(['property_id', 'user_id']);
        });
        Schema::create('property_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->date('day');
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
            $table->unique(['property_id', 'day']);
        });
        Schema::create('contact_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('body');
            $table->boolean('handled')->default(false);
            $table->timestamps();
        });
        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['subscribers', 'contact_submissions', 'property_views', 'reviews', 'messages', 'conversations', 'favorites', 'amenity_property', 'amenities', 'property_photos', 'properties'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'company', 'position', 'office_address', 'avatar_path', 'bio', 'social_links']);
        });
    }
};
