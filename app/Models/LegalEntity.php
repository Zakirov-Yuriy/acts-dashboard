<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegalEntity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'inn', 'kpp', 'ogrn', 'bank_account', 'bank_name', 'contact_person',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /** Оплаты, где это юрлицо выступает плательщиком. */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
