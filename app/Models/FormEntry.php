<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormEntry extends Model
{
    protected $fillable = ['form_id', 'user_id', 'status_id', 'source'];

    // Form-ul completat
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(FormEntryStatus::class, 'status_id');
    }

    // Valorile completate
    public function values(): HasMany
    {
        return $this->hasMany(FormEntryValue::class);
    }

    // Helper: returnează valoarea pentru un câmp specific
    public function valueFor(int $columnId): ?string
    {
        return $this->values
            ->firstWhere('form_column_id', $columnId)
            ?->value;
    }
}
