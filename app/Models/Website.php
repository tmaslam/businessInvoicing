<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    protected $table = 'Websites';

    protected $primaryKey = 'ID';

    public $timestamps = false;
}
