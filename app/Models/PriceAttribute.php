<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceAttribute extends Model
{
    protected $table = 'CustomerPriceAttribute';

    protected $primaryKey = 'ID';

    public $timestamps = false;

    protected $fillable = [
        'No_of_Stiches_Start', 'No_of_Stiches_End', 'Price_per_Thousand_Stiches', 'Is_Fixed', 'Customer_ID',
    ];
}
