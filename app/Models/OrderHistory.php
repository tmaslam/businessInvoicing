<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    protected $table = 'OrderHistory';

    protected $primaryKey = 'ID';

    public $timestamps = false;

    protected $fillable = ['Order_ID', 'Edit_Order_Staus_ID'];
}
