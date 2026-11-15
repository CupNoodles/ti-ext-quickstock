<?php

declare(strict_types=1);

namespace CupNoodles\QuickStock\Classes;

use Carbon\Carbon;
use Exception;
use Igniter\Cart\CartItem;
use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\MenuItemOptionValue;
use Igniter\Cart\Models\MenuOptionValue;
use Igniter\Cart\Models\Stock;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Local\Models\Location;

/**
 * Marks menu items and option values out of stock at a location using TastyIgniter's own
 * stock overrides (the stocks table), so core's hourly job expires them and, for tracked
 * stock, the cart and Inventory page see them too.
 *
 * Unlike Inventory, this doesn't require stock tracking, which suits kitchens that only "86"
 * items. Core only checks *tracked* stock (Stockable::outOfStock()), so overrides on untracked
 * stock are enforced here: see isOutOfStock() and assertCartItemInStock().
 */
class StockOverride
{
    /** Morph alias => model. Option values are shared, so 86-ing one affects every menu item using it. */
    public const array STOCKABLES = [
        'menus' => Menu::class,
        'menu_option_values' => MenuOptionValue::class,
    ];

    /**
     * Same choices as the Inventory page.
     *
     * @param string $type One of: duration, closing, indefinitely, custom
     */
    public static function markOutOfStock(Stock $stock, string $type, array $options = []): Stock
    {
        [$overrideType, $untilDate] = match ($type) {
            Stock::OOS_INDEFINITELY => [Stock::OOS_INDEFINITELY, null],
            'duration' => [Stock::OOS_CUSTOM, self::resolveDuration((int)($options['duration'] ?? 0))],
            'closing' => [Stock::OOS_CUSTOM, self::resolveClosingTime($stock->location)],
            Stock::OOS_CUSTOM => [Stock::OOS_CUSTOM, self::resolveCustomDate($options['until'] ?? null)],
            default => throw new ApplicationException(lang('igniter.cart::default.stocks.alert_invalid_oos_type')),
        };

        // Not Stock::applyOutOfStockOverride(): that refuses untracked stock
        $stock->out_of_stock_type = $overrideType;
        $stock->out_of_stock_until = $untilDate;
        $stock->saveQuietly();

        $stock->fireSystemEvent('admin.stock.outOfStock', [$stock]);

        return $stock;
    }

    public static function clear(Stock $stock): Stock
    {
        $stock->clearOutOfStockOverride();

        return $stock;
    }

    /**
     * Out of stock at the location by core's rules (tracked quantity or override) or by an
     * override on untracked stock.
     */
    public static function isOutOfStock(Model $stockable, Location|int|null $location): bool
    {
        if (is_null($location)) {
            return false;
        }

        $locationId = $location instanceof Location ? $location->getKey() : $location;

        if ($stockable->outOfStock($locationId)) {
            return true;
        }

        return $stockable->stocks
            ->where('location_id', $locationId)
            ->contains(fn(Stock $stock): bool => $stock->hasOutOfStockOverride());
    }

    /**
     * Throws if the cart item, or any option value chosen for it, is out of stock.
     */
    public static function assertCartItemInStock(CartItem $cartItem, Location|int|null $location): void
    {
        $menu = $cartItem->model;
        if ($menu instanceof Menu && self::isOutOfStock($menu, $location)) {
            throw new ApplicationException(sprintf(lang('igniter.cart::default.alert_out_of_stock'), $menu->getBuyableName()));
        }

        foreach ($cartItem->options ?? [] as $cartOption) {
            foreach ($cartOption->values ?? [] as $cartValue) {
                $optionValue = MenuItemOptionValue::find($cartValue->id)?->option_value;
                if ($optionValue && self::isOutOfStock($optionValue, $location)) {
                    throw new ApplicationException(sprintf(lang('igniter.cart::default.alert_out_of_stock'), $cartValue->name));
                }
            }
        }
    }

    protected static function resolveDuration(int $minutes): Carbon
    {
        throw_unless($minutes > 0, new ApplicationException(lang('igniter.cart::default.stocks.alert_invalid_oos_duration')));

        return now()->addMinutes($minutes);
    }

    protected static function resolveClosingTime(Location $location): Carbon
    {
        try {
            $closeAt = $location->newWorkingSchedule('opening')->nextCloseAt(now());
        } catch (Exception) {
            $closeAt = null;
        }

        throw_unless($closeAt, new ApplicationException(lang('igniter.cart::default.stocks.alert_no_closing_time')));

        return Carbon::instance($closeAt);
    }

    protected static function resolveCustomDate(?string $date): Carbon
    {
        try {
            $untilDate = Carbon::parse((string)$date);
        } catch (Exception) {
            $untilDate = null;
        }

        throw_unless($date && $untilDate?->isFuture(), new ApplicationException(
            lang('cupnoodles.quickstock::default.alert_invalid_date'),
        ));

        return $untilDate;
    }
}
