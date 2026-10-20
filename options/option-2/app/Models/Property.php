<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'description', 'address', 'location', 'state', 'country', 'zip_code', 'neighborhood', 'type', 'purpose', 'price', 'rental_period', 'area', 'land_area', 'rooms', 'bedrooms', 'bathrooms', 'garages', 'garage_area', 'year_built', 'latitude', 'longitude', 'availability'];

    protected static function booted(): void
    {
        static::creating(function (Property $property): void {
            $property->reference ??= 'IE-'.strtoupper((string) Str::ulid());
        });
    }

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'is_demo' => 'boolean', 'presentation' => 'array', 'source_details' => 'array', 'imported_at' => 'datetime'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PropertyPhoto::class)->orderBy('position')->orderBy('id');
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(PropertyView::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', 'published')->where('availability', 'available')->whereRaw('LOWER(TRIM(location)) = ?', ['indore']);
    }

    public function scopeWithPublicRatings(Builder $query): Builder
    {
        return $query->withCount(['reviews as approved_reviews_count' => fn ($reviews) => $reviews->where('status', 'approved')])
            ->withAvg(['reviews as approved_rating' => fn ($reviews) => $reviews->where('status', 'approved')], 'rating');
    }

    public function isPublic(): bool
    {
        return ! $this->trashed() && $this->status === 'published' && $this->availability === 'available' && $this->isInIndore();
    }

    public function isInIndore(): bool
    {
        return mb_strtolower(trim((string) $this->location)) === 'indore';
    }

    public function priceLabel(): string
    {
        if ($this->price === null) {
            return 'Price on request';
        }
        $formatter = new \NumberFormatter('en_IN', \NumberFormatter::DECIMAL);
        $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 0);
        $details = $this->presentation ?? [];
        $label = '₹'.$formatter->format((float) $this->price);
        if (($details['unit_price'] ?? 'total') === 'per_sqft') {
            $label .= ' / sqft';
        }
        if ($this->purpose === 'rent') {
            $label .= ' / '.($this->rental_period ?: 'month');
        }

        return trim(($details['before_price_label'] ?? '').' '.$label.' '.($details['after_price_label'] ?? ''));
    }

    public function coverUrl(): string
    {
        return $this->photos->first()?->url() ?? asset('branding/property-placeholder.svg');
    }
}
