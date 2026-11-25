<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Non-destructive upgrades to the existing Business Invoicing database.
 * No data is removed or rewritten:
 *  - User.Password widened so bcrypt hashes fit (legacy column was 50 chars).
 *  - User.remember_token added for "Remember me".
 *  - Indexes on the columns every listing/report filters on (Orders has 270k+ rows).
 */
return new class extends Migration
{
    private array $orderIndexes = [
        'orders_customer_idx' => ['Customer_ID'],
        'orders_status_effective_idx' => ['Order_Status', 'Effective_Date'],
        'orders_completed_date_idx' => ['Completed_Date'],
        'orders_completed_by_idx' => ['Completed_By', 'Completed_Date'],
        'orders_assign_by_idx' => ['Assign_By'],
        'orders_qa_by_idx' => ['QA_By'],
        'orders_design_name_idx' => ['Design_Name'],
    ];

    public function up(): void
    {
        DB::statement('ALTER TABLE `User` MODIFY `Password` VARCHAR(255) NULL DEFAULT NULL');

        if (! Schema::hasColumn('User', 'remember_token')) {
            Schema::table('User', function (Blueprint $table) {
                $table->string('remember_token', 100)->nullable();
            });
        }

        foreach ($this->orderIndexes as $name => $columns) {
            if (! $this->indexExists('Orders', $name)) {
                Schema::table('Orders', fn (Blueprint $table) => $table->index($columns, $name));
            }
        }

        if (! $this->indexExists('OrderHistory', 'order_history_order_idx')) {
            Schema::table('OrderHistory', fn (Blueprint $table) => $table->index(['Order_ID'], 'order_history_order_idx'));
        }
        if (! $this->indexExists('CustomerPriceAttribute', 'price_attr_customer_idx')) {
            Schema::table('CustomerPriceAttribute', fn (Blueprint $table) => $table->index(['Customer_ID'], 'price_attr_customer_idx'));
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->orderIndexes) as $name) {
            if ($this->indexExists('Orders', $name)) {
                Schema::table('Orders', fn (Blueprint $table) => $table->dropIndex($name));
            }
        }
        if ($this->indexExists('OrderHistory', 'order_history_order_idx')) {
            Schema::table('OrderHistory', fn (Blueprint $table) => $table->dropIndex('order_history_order_idx'));
        }
        if ($this->indexExists('CustomerPriceAttribute', 'price_attr_customer_idx')) {
            Schema::table('CustomerPriceAttribute', fn (Blueprint $table) => $table->dropIndex('price_attr_customer_idx'));
        }
        if (Schema::hasColumn('User', 'remember_token')) {
            Schema::table('User', fn (Blueprint $table) => $table->dropColumn('remember_token'));
        }
        // Password column is intentionally left at 255 chars: hashed values would not fit in 50.
    }

    private function indexExists(string $table, string $name): bool
    {
        return collect(Schema::getIndexes($table))->contains(fn ($i) => strcasecmp($i['name'], $name) === 0);
    }
};
