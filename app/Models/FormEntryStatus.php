<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormEntryStatus extends Model
{
    protected $fillable = ['owner_id', 'name', 'color', 'order'];

    // color stores a hex string, e.g. '#3b82f6'

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(FormEntry::class, 'status_id');
    }
}
