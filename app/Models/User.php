<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

/**
 * Maps the legacy `User` table.
 *
 * @property int $User_Id
 * @property string $User_Name
 * @property string|null $Password
 * @property int $Role_ID
 */
class User extends Authenticatable
{
    protected $table = 'User';

    protected $primaryKey = 'User_Id';

    public $timestamps = false;

    protected $authPasswordName = 'Password';

    protected $fillable = [
        'User_Name', 'FName', 'LName', 'Contact_No', 'Address', 'Email',
        'Role_ID', 'Profit_Distribution', 'Joining_Date', 'active_user', 'Session_ID',
    ];

    protected $hidden = ['Password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'Role_ID' => 'integer',
            'Profit_Distribution' => 'float',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('End_Date');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'Role_ID', 'ID');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->FName.' '.$this->LName);
    }

    public function getRoleNameAttribute(): string
    {
        return config('binvoice.role_names')[$this->Role_ID] ?? 'Unknown';
    }

    public function hasRole(int|string ...$roles): bool
    {
        $ids = array_map(fn ($r) => is_numeric($r) ? (int) $r : config("binvoice.roles.$r"), $roles);

        return in_array($this->Role_ID, $ids, true);
    }

    public function isAdmin(): bool
    {
        return $this->Role_ID === config('binvoice.roles.admin');
    }

    public function isBlocked(): bool
    {
        return (string) $this->active_user !== '1';
    }

    /**
     * Legacy passwords were stored in plain text. Accept them once and
     * transparently upgrade to a bcrypt hash.
     */
    public function verifyAndUpgradePassword(string $plain): bool
    {
        $stored = (string) $this->Password;
        if ($stored === '') {
            return false;
        }

        if (password_get_info($stored)['algo'] !== null) {
            if (! Hash::check($plain, $stored)) {
                return false;
            }
            if (Hash::needsRehash($stored)) {
                $this->setPassword($plain);
            }

            return true;
        }

        if (! hash_equals($stored, $plain)) {
            return false;
        }
        $this->setPassword($plain);

        return true;
    }

    public function setPassword(string $plain): void
    {
        $this->forceFill(['Password' => Hash::make($plain)])->saveQuietly();
    }
}
