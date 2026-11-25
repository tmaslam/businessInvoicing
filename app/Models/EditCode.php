<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EditCode extends Model
{
    protected $table = 'EditCode';

    protected $primaryKey = 'Code_ID';

    public $timestamps = false;

    protected $fillable = ['Narrative', 'Code_Type', 'Effective_Date', 'Session_ID'];

    protected function casts(): array
    {
        return ['Effective_Date' => 'datetime', 'Time_Stamp' => 'datetime'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('End_Date');
    }
}
