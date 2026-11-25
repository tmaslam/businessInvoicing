<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Legacy `SessionID` audit table. Every write stores the returned
 * Session_ID on the affected row, exactly like the old application.
 */
class ActionLog extends Model
{
    protected $table = 'SessionID';

    protected $primaryKey = 'Session_ID';

    public $timestamps = false;

    protected $fillable = ['User_ID', 'IP_Address', 'Action', 'Effective_Date'];

    public static function record(string $action): int
    {
        return (int) static::create([
            'User_ID' => (string) (auth()->id() ?? 0),
            'IP_Address' => substr((string) request()->ip(), 0, 15),
            'Action' => $action,
            'Effective_Date' => now(),
        ])->Session_ID;
    }
}
