<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $table = 'Orders';

    protected $primaryKey = 'Order_ID';

    public $timestamps = false;

    protected $fillable = [
        'Customer_ID', 'Design_Name', 'No_Of_Stiches', 'Order_Price', 'Order_Status_ID',
        'Completed_By', 'Completed_Date', 'Effective_Date', 'Session_ID', 'Order_Status',
        'Assign_By', 'QA_By',
    ];

    protected function casts(): array
    {
        return [
            'Effective_Date' => 'datetime',
            'Completed_Date' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('Orders.End_Date');
    }

    public function scopeStatus(Builder $query, string $key): Builder
    {
        return $query->where('Orders.Order_Status', config("binvoice.order_status.$key"));
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'Customer_ID', 'Customer_ID');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Assign_By', 'User_Id');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Completed_By', 'User_Id');
    }

    public function qaUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'QA_By', 'User_Id');
    }

    public function editCode(): BelongsTo
    {
        return $this->belongsTo(EditCode::class, 'Order_Status_ID', 'Code_ID');
    }

    public function isVerified(): bool
    {
        return (int) $this->QA_By !== 0;
    }

    /** Human readable "x days, y hours, z minutes" between creation and completion. */
    public function completionDuration(): string
    {
        if (! $this->Effective_Date || ! $this->Completed_Date) {
            return 'N/A';
        }
        $diff = $this->Effective_Date->diff($this->Completed_Date);
        $parts = [];
        if ($diff->days > 0) {
            $parts[] = $diff->days.' days';
        }
        if ($diff->h > 0) {
            $parts[] = $diff->h.' hours';
        }
        if ($diff->i > 0) {
            $parts[] = $diff->i.' minutes';
        }

        return $parts ? implode(', ', $parts) : 'less than a minute';
    }
}
