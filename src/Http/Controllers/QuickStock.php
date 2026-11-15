<?php

declare(strict_types=1);

namespace CupNoodles\QuickStock\Http\Controllers;

use CupNoodles\QuickStock\Classes\StockOverride;
use CupNoodles\QuickStock\Models\QuickStockSettings;
use Igniter\Admin\Classes\AdminController;
use Igniter\Admin\Facades\AdminMenu;
use Igniter\Admin\Facades\Template;
use Igniter\Cart\Models\Category;
use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\Stock;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Local\Facades\Location as LocationFacade;
use Igniter\Local\Models\Location;
use Illuminate\Support\Collection;

/**
 * Quick 86: every menu item (and optionally its option values) for one location on one page,
 * grouped by category, each with one-click out-of-stock controls.
 */
class QuickStock extends AdminController
{
    protected null|string|array $requiredPermissions = 'CupNoodles.QuickStock.Manage';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('quickstock', 'restaurant');
    }

    public function index(): void
    {
        $title = lang('cupnoodles.quickstock::default.side_menu');
        Template::setTitle($title);
        Template::setHeading($title);

        $this->prepareVars();
    }

    /**
     * Same request parameters as the Inventory page, so core's stock status partial can be reused.
     */
    public function index_onMarkOutOfStock(): array
    {
        $stock = $this->findStock();

        StockOverride::markOutOfStock($stock, (string)post('outOfStockType'), [
            'duration' => post('outOfStockDuration'),
            'until' => post('outOfStockUntil'),
        ]);

        flash()->success(lang('igniter.cart::default.stocks.alert_marked_as_out_of_stock'));

        return $this->refreshPanel($stock->location);
    }

    public function index_onClearOutOfStock(): array
    {
        $stock = $this->findStock();

        StockOverride::clear($stock);

        flash()->success(lang('igniter.cart::default.stocks.alert_back_in_stock'));

        return $this->refreshPanel($stock->location);
    }

    protected function refreshPanel(Location $location): array
    {
        $this->prepareVars($location);

        return ['#quickstock-panel' => $this->renderPanel()];
    }

    /**
     * The panel uses core's Inventory status control for each row. Two adjustments for this page:
     * - no "are you sure?" prompt on the out-of-stock options (this page is for quick changes)
     * - core's custom date & time dialog once, not once per row (its @once resets per partial)
     * If core's markup changes, the prompts simply stay and the extra dialogs are harmless.
     */
    public function renderPanel(): string
    {
        $html = $this->makePartial('panel');

        $html = preg_replace('/(data-request="onMarkOutOfStock"[^>]*?)\s+data-request-confirm="[^"]*"/', '$1', $html);

        $seen = false;

        return preg_replace_callback(
            '/<div class="modal fade" id="stockOosModal".*?<\/script>/s',
            function(array $match) use (&$seen): string {
                if ($seen) {
                    return '';
                }

                $seen = true;

                return $match[0];
            },
            $html,
        );
    }

    /**
     * A menu item or option value stock record at a location this admin manages.
     */
    protected function findStock(): Stock
    {
        $stock = Stock::query()
            ->whereKey((int)post('recordId'))
            ->whereIn('stockable_type', array_keys(StockOverride::STOCKABLES))
            ->whereIn('location_id', $this->listLocations()->modelKeys())
            ->first();

        throw_unless($stock, new ApplicationException(lang('cupnoodles.quickstock::default.alert_invalid_item')));

        return $stock;
    }

    protected function prepareVars(?Location $location = null): void
    {
        $locations = $this->listLocations();
        $location ??= $locations->firstWhere('location_id', (int)request('location'))
            ?? $locations->firstWhere('location_id', LocationFacade::getId())
            ?? $locations->first();

        $this->vars['locations'] = $locations;
        $this->vars['location'] = $location;
        $this->vars['showOptions'] = (bool)QuickStockSettings::get('quickstock_options', false);
        $this->vars['categories'] = $location ? $this->listCategories($location) : collect();
        $this->vars['stocks'] = $location
            ? $this->loadStocks($location, $this->vars['categories'], $this->vars['showOptions'])
            : collect();
        $this->vars['useDocketNames'] = (bool)QuickStockSettings::get('print_docket_names', false);
    }

    /**
     * Locations this admin can manage; super admins see all enabled locations.
     */
    protected function listLocations(): Collection
    {
        $query = Location::query()->whereIsEnabled()->orderBy('location_name');

        if ($ids = LocationFacade::currentOrAssigned()) {
            $query->whereIn('location_id', $ids);
        }

        return $query->get();
    }

    /**
     * Enabled menu items available at the location, grouped by category in storefront order.
     * Items in no category are listed last.
     *
     * @return Collection<int, array{name: string, menus: Collection}>
     */
    protected function listCategories(Location $location): Collection
    {
        $menus = Menu::query()
            ->whereIsEnabled()
            ->whereHasOrDoesntHaveLocation($location->getKey())
            ->with(['categories', 'menu_options.menu_option_values.option_value'])
            ->orderBy('menu_priority')
            ->orderBy('menu_name')
            ->get();

        $groups = Category::query()
            ->whereIsEnabled()
            ->orderBy('priority')
            ->orderBy('name')
            ->get()
            ->map(fn(Category $category): array => [
                'name' => $category->name,
                'menus' => $menus->filter(fn(Menu $menu): bool => $menu->categories->contains($category)),
            ])
            ->push([
                'name' => lang('cupnoodles.quickstock::default.text_uncategorised'),
                'menus' => $menus->filter(fn(Menu $menu): bool => $menu->categories->isEmpty()),
            ])
            ->filter(fn(array $group): bool => $group['menus']->isNotEmpty())
            ->values();

        if (QuickStockSettings::get('order_alphabetical', false)) {
            $useDocketNames = (bool)QuickStockSettings::get('print_docket_names', false);
            $groups = $groups->map(function(array $group) use ($useDocketNames): array {
                $group['menus'] = $group['menus']->sortBy(
                    fn(Menu $menu): string => self::displayName($menu, $useDocketNames),
                    SORT_NATURAL | SORT_FLAG_CASE,
                );

                return $group;
            });
        }

        return $groups;
    }

    /**
     * The location's stock record for every listed item, keyed "stockable_type:stockable_id".
     * Missing records are created untracked, as core does when an item's stock is first viewed.
     */
    protected function loadStocks(Location $location, Collection $categories, bool $withOptions): Collection
    {
        $menus = $categories->flatMap(fn(array $group) => $group['menus'])->unique('menu_id');
        $wanted = $menus->map(fn(Menu $menu): array => ['menus', $menu->getKey()]);
        if ($withOptions) {
            $wanted = $wanted->merge(self::optionValuesFor($menus)->map(fn($value): array => ['menu_option_values', $value->getKey()]));
        }

        $query = fn() => Stock::query()
            ->with('location')
            ->where('location_id', $location->getKey())
            ->whereIn('stockable_type', array_keys(StockOverride::STOCKABLES))
            ->get()
            ->keyBy(fn(Stock $stock): string => $stock->stockable_type.':'.$stock->stockable_id);

        $stocks = $query();
        $missing = $wanted->reject(fn(array $item): bool => $stocks->has($item[0].':'.$item[1]));
        if ($missing->isNotEmpty()) {
            Stock::insert($missing->map(fn(array $item): array => [
                'location_id' => $location->getKey(),
                'stockable_type' => $item[0],
                'stockable_id' => $item[1],
                'created_at' => now(),
                'updated_at' => now(),
            ])->values()->all());
            $stocks = $query();
        }

        return $stocks;
    }

    /**
     * Option values offered by the menu items, each listed once.
     */
    public static function optionValuesFor(Collection $menus): Collection
    {
        return $menus->flatMap(fn(Menu $menu) => $menu->menu_options)
            ->flatMap(fn($option) => $option->menu_option_values)
            ->pluck('option_value')
            ->filter()
            ->unique('option_value_id')
            ->values();
    }

    /**
     * The print docket name comes from a printer extension when one adds that column.
     */
    public static function displayName(Menu $menu, bool $useDocketNames): string
    {
        return ($useDocketNames && $menu->getAttribute('print_docket'))
            ? (string)$menu->getAttribute('print_docket')
            : (string)$menu->menu_name;
    }
}
