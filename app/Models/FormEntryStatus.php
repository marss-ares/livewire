<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormEntryStatus extends Model
{
    protected $fillable = ['name', 'color', 'order'];

    public function entries(): HasMany
    {
        return $this->hasMany(FormEntry::class, 'status_id');
    }
}
