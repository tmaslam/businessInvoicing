<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Design-name searches use LIKE '%term%', which can't seek an index.
 * A covering index lets MySQL filter on name + status + soft-delete by
 * scanning the (much smaller) index instead of the whole Orders table,
 * and only read the rows that actually match.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! $this->has('orders_design_search_idx')) {
            Schema::table('Orders', fn (Blueprint $t) => $t->index(
                ['Design_Name', 'End_Date', 'Order_Status', 'Customer_ID', 'Completed_By'],
                'orders_design_search_idx'
            ));
        }
        if ($this->has('orders_design_name_idx')) {
            Schema::table('Orders', fn (Blueprint $t) => $t->dropIndex('orders_design_name_idx'));
        }
    }

    public function down(): void
    {
        if (! $this->has('orders_design_name_idx')) {
            Schema::table('Orders', fn (Blueprint $t) => $t->index(['Design_Name'], 'orders_design_name_idx'));
        }
        if ($this->has('orders_design_search_idx')) {
            Schema::table('Orders', fn (Blueprint $t) => $t->dropIndex('orders_design_search_idx'));
        }
    }

    private function has(string $name): bool
    {
        return collect(Schema::getIndexes('Orders'))->contains(fn ($i) => strcasecmp($i['name'], $name) === 0);
    }
};
