<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $table = 'Expense';

    protected $primaryKey = 'ID';

    public $timestamps = false;

    protected $fillable = ['Narrative', 'Amount', 'Type', 'Expense_Month', 'Session_ID'];

    protected function casts(): array
    {
        return ['Expense_Month' => 'datetime', 'Amount' => 'float'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('End_Date');
    }
}
