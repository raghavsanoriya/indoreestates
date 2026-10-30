<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteContentRevision extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['key', 'value', 'was_default', 'user_id'];

    protected function casts(): array
    {
        return ['was_default' => 'boolean'];
    }
}
