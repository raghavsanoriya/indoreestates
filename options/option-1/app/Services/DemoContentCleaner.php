<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Property;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DemoContentCleaner
{
    /** @param array<int, int> $propertyIds */
    public function candidates(array $propertyIds = []): Builder
    {
        return Property::withTrashed()->whereNull('source_key')->where(function (Builder $query) use ($propertyIds): void {
            $query->where('is_demo', true)->orWhereIn('id', $propertyIds);
        });
    }

    /** @param array<int, int> $propertyIds */
    public function clean(array $propertyIds = []): int
    {
        return DB::transaction(function () use ($propertyIds): int {
            $ids = $this->candidates($propertyIds)->pluck('id');
            Conversation::whereIn('property_id', $ids)->delete();

            return Property::withTrashed()->whereIn('id', $ids)->forceDelete();
        });
    }
}
