<?php

declare(strict_types=1);

namespace CupNoodles\QuickStock;

use CupNoodles\QuickStock\Classes\StockOverride;
use CupNoodles\QuickStock\Models\QuickStockSettings;
use Igniter\Cart\CartItem;
use Igniter\Cart\Classes\CartManager;
use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\MenuOptionValue;
use Igniter\Local\Facades\Location;
use Igniter\System\Classes\BaseExtension;
use Illuminate\Support\Facades\Event;
use Override;

/**
 * Out-of-stock state and expiry use TastyIgniter's own stock overrides (igniter.cart stocks).
 * This extension adds the Quick 86 page, and enforces overrides on untracked stock, which
 * core's cart checks skip.
 */
class Extension extends BaseExtension
{
    #[Override]
    public function boot(): void
    {
        // Theme helper: $menuItem->isOutOfStock($locationId), same rules the cart enforces
        foreach ([Menu::class, MenuOptionValue::class] as $modelClass) {
            $modelClass::extend(function($model): void {
                $model->addDynamicMethod('isOutOfStock', fn($location = null): bool => StockOverride::isOutOfStock($model, $location ?? Location::getId()));
            });
        }

        $checkCartItem = function($cart, CartItem $cartItem): void {
            StockOverride::assertCartItemInStock($cartItem, Location::getId());
        };
        Event::listen('cart.adding', $checkCartItem);
        Event::listen('cart.updating', $checkCartItem);

        // Items can go out of stock after they were added to the cart
        Event::listen('igniter.checkout.beforeSaveOrder', function(): void {
            foreach (resolve(CartManager::class)->getCart()->content() as $cartItem) {
                StockOverride::assertCartItemInStock($cartItem, Location::getId());
            }
        });
    }

    #[Override]
    public function registerPermissions(): array
    {
        return [
            'CupNoodles.QuickStock.Manage' => [
                'description' => 'lang:cupnoodles.quickstock::default.permission_manage',
                'group' => 'igniter::admin.permissions.name',
            ],
        ];
    }

    #[Override]
    public function registerNavigation(): array
    {
        return [
            'restaurant' => [
                'child' => [
                    'quickstock' => [
                        'priority' => 25,
                        'class' => 'quickstock',
                        'href' => admin_url('cupnoodles/quickstock/quick_stock'),
                        'title' => lang('cupnoodles.quickstock::default.side_menu'),
                        'permission' => 'CupNoodles.QuickStock.Manage',
                    ],
                ],
            ],
        ];
    }

    #[Override]
    public function registerSettings(): array
    {
        return [
            'settings' => [
                'label' => 'lang:cupnoodles.quickstock::default.text_settings',
                'description' => 'lang:cupnoodles.quickstock::default.text_settings_desc',
                'icon' => 'fa fa-ban',
                'model' => QuickStockSettings::class,
                'permissions' => ['CupNoodles.QuickStock.Manage'],
            ],
        ];
    }
}
