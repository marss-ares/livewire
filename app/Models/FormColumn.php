<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormColumn extends Model
{
    protected $fillable = [
        'form_id', 'name', 'key', 'type',
        'options', 'required', 'order', 'is_system',
    ];

    protected $casts = [
        'options'   => 'array',
        'required'  => 'boolean',
        'order'     => 'integer',
        'is_system' => 'boolean',
    ];

    // Form-ul căruia îi aparține câmpul
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    // Valorile completate pentru acest câmp
    public function entryValues(): HasMany
    {
        return $this->hasMany(FormEntryValue::class);
    }
}
