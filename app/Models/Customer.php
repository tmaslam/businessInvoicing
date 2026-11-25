<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $table = 'Customers';

    protected $primaryKey = 'Customer_ID';

    public $timestamps = false;

    protected $fillable = [
        'Customer_Unique_ID', 'CompanyName', 'ContactPerson', 'Email', 'Address', 'Zip_Code',
        'City', 'State', 'Country', 'Phone', 'Is_Active', 'Normal_Fee', 'Urgent_Fee',
        'Min_Order_Value', 'Max_Order_Value', 'Currency', 'Website_ID', 'Session_ID',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('End_Date');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'Customer_ID', 'Customer_ID');
    }

    public function priceAttributes(): HasMany
    {
        return $this->hasMany(PriceAttribute::class, 'Customer_ID', 'Customer_ID')
            ->orderBy('No_of_Stiches_Start')->orderBy('No_of_Stiches_End');
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class, 'Website_ID', 'ID');
    }

    public function websitePrefix(): string
    {
        return config('binvoice.websites')[(int) $this->Website_ID]['prefix'] ?? 'DZ';
    }

    public function websiteName(): string
    {
        return $this->website?->Narrative
            ?? (config('binvoice.websites')[(int) $this->Website_ID]['name'] ?? '');
    }

    /** Legacy customer code, e.g. DZ0012. */
    public function generateUniqueCode(): string
    {
        return $this->websitePrefix().'00'.$this->Customer_ID;
    }
}
