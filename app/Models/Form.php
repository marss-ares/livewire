<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    protected $fillable = ['user_id', 'name', 'description'];

    // Owner-ul formului
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Coloanele (câmpurile) definite în form
    public function columns(): HasMany
    {
        return $this->hasMany(FormColumn::class)->orderBy('order');
    }

    // Înregistrările (completările) trimise
    public function entries(): HasMany
    {
        return $this->hasMany(FormEntry::class);
    }
}
