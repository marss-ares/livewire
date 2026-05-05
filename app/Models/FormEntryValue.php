<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormEntryValue extends Model
{
    protected $fillable = ['form_entry_id', 'form_column_id', 'value'];

    // Înregistrarea căreia îi aparține valoarea
    public function entry(): BelongsTo
    {
        return $this->belongsTo(FormEntry::class, 'form_entry_id');
    }

    // Coloana (câmpul) pentru care e valoarea
    public function column(): BelongsTo
    {
        return $this->belongsTo(FormColumn::class, 'form_column_id');
    }
}
