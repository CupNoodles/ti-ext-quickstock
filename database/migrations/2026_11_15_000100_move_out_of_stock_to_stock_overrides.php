<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * v3 Quick 86 kept out-of-stock flags in `locationables` rows of type menu_out_of_stock /
 * option_value_out_of_stock, with the back-in-stock date (m/d/Y, or '' for indefinitely) in
 * `options`. v4 uses TastyIgniter's own per-location stock overrides instead.
 *
 * Expired flags are dropped. down() copies current overrides back to v3 rows (it leaves the
 * overrides in place, as some may have been set on the Inventory page).
 */
return new class extends Migration
{
    protected array $types = [
        'menu_out_of_stock' => 'menus',
        'option_value_out_of_stock' => 'menu_option_values',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('locationables') || !Schema::hasColumn('stocks', 'out_of_stock_type')) {
            return;
        }

        $rows = DB::table('locationables')->whereIn('locationable_type', array_keys($this->types))->get();

        foreach ($rows as $row) {
            $until = $this->parseLegacyDate((string)$row->options);
            if ($until && $until->isPast()) {
                continue;
            }

            $match = [
                'location_id' => $row->location_id,
                'stockable_type' => $this->types[$row->locationable_type],
                'stockable_id' => $row->locationable_id,
            ];

            $values = [
                'out_of_stock_type' => $until ? 'custom' : 'indefinitely',
                'out_of_stock_until' => $until,
                'updated_at' => now(),
            ];

            if (DB::table('stocks')->where($match)->exists()) {
                DB::table('stocks')->where($match)->update($values);
            } else {
                DB::table('stocks')->insert($match + $values + ['created_at' => now()]);
            }
        }

        DB::table('locationables')->whereIn('locationable_type', array_keys($this->types))->delete();
    }

    public function down(): void
    {
        if (!Schema::hasTable('locationables') || !Schema::hasColumn('stocks', 'out_of_stock_type')) {
            return;
        }

        $overrides = DB::table('stocks')
            ->whereIn('stockable_type', array_values($this->types))
            ->whereNotNull('out_of_stock_type')
            ->get();

        $legacyTypes = array_flip($this->types);
        foreach ($overrides as $stock) {
            DB::table('locationables')->updateOrInsert([
                'location_id' => $stock->location_id,
                'locationable_id' => $stock->stockable_id,
                'locationable_type' => $legacyTypes[$stock->stockable_type],
            ], [
                'options' => $stock->out_of_stock_until ? Carbon::parse($stock->out_of_stock_until)->format('m/d/Y') : '',
            ]);
        }
    }

    protected function parseLegacyDate(string $value): ?Carbon
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        try {
            // v3 stored m/d/Y and treated the item as back in stock from the start of that day
            return Carbon::createFromFormat('m/d/Y', $value)->startOfDay();
        } catch (Throwable) {
            return null; // unreadable date: keep it out of stock rather than silently restock
        }
    }
};
