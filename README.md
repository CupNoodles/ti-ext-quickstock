# Quick 86 for TastyIgniter

One admin page for "86-ing" menu items: every item at a location, grouped by category, each with
TastyIgniter's own **Mark Out of Stock** control. Pick how long it's out (30 minutes to 4 hours,
until closing time, indefinitely or a custom date and time). Click the **×** next to its badge to put
it back in stock.

Quick 86 uses TastyIgniter's own stock overrides, the same ones as **Restaurant > Inventory**.
Out-of-stock items can't be added to the cart and overrides expire on their own. You don't need to
track stock quantities to use it, so it works for kitchens that only mark things in and out. If you
track quantities later, Quick 86 keeps working alongside them (the Inventory page only lists items
whose stock is tracked).

> Using TastyIgniter v3? Use the [`v3` branch](https://github.com/CupNoodles/ti-ext-quickstock/tree/v3).

## Requirements

- TastyIgniter v4 with the Cart extension v4.5 or later (for stock overrides)
- Local extension (installed with TastyIgniter by default)
- Optional: the task scheduler (`php artisan schedule:run` every minute from cron), which
  TastyIgniter uses to tidy up expired overrides. Items come back in stock on time without it.

## Installation

### From GitHub (git clone)

From your TastyIgniter root folder, clone the extension into `extensions/cupnoodles/quickstock`. The
folder name matters: TastyIgniter finds extensions at `extensions/<vendor>/<name>`.

```bash
git clone https://github.com/CupNoodles/ti-ext-quickstock.git extensions/cupnoodles/quickstock
php artisan igniter:up
```

To update later:

```bash
cd extensions/cupnoodles/quickstock
git pull
cd ../../..
php artisan igniter:up
```

### With Composer, from GitHub

```bash
composer config repositories.cupnoodles-quickstock vcs https://github.com/CupNoodles/ti-ext-quickstock
composer require cupnoodles/ti-ext-quickstock:dev-master
php artisan igniter:up
```

Once a version is tagged, you can require `^4.0` instead of `dev-master`.

## Usage

Open **Restaurant > Quick 86**. Admins who manage several locations get a tab per location.

- **Mark Out of Stock** marks an item out of stock at that location. Choose how long from the menu.
- The **×** next to an "Out of stock" badge puts it back in stock straight away.

These are the same controls as the Inventory page. Items that are out of stock because a tracked
quantity ran out show a badge too; manage their stock level on the Inventory page.

### Settings

Under **System > Settings > Quick 86 Settings**:

- **Show menu options**: lists option values (sizes, add-ons) under each item so they can be 86'd
  too. An option value is shared: marking "Diet Coke" out of stock affects every item that offers it.
- **Sort menu items alphabetically** instead of in online-menu order.
- **Use print docket names**: shows the kitchen docket name instead of the menu name, when a printer
  extension adds one.

## Showing out-of-stock items in your theme

Out-of-stock items and option values can't be added to the cart. To show them as unavailable on the
menu, use `isOutOfStock()` in your theme. It gives the same answer the cart uses, including items you
don't track quantities for:

```blade
@if ($menuItem->isOutOfStock($locationId))
    <span class="badge bg-secondary">Sold out</span>
@endif
```

TastyIgniter's own `outOfStock()` only looks at items with stock tracking switched on.

## Upgrading from v3

Run `php artisan igniter:up`. Items currently marked out of stock are moved to TastyIgniter's stock
overrides. Entries whose back-in-stock date has passed are dropped. Your settings are kept.

Differences from v3:

- The template method is now `isOutOfStock($locationId)`. The second (order time) argument is gone.
- Out-of-stock is checked against the current time, not the order's date. In v3, a pre-order for a
  date after the back-in-stock date was allowed; now the item can't be ordered until it's back in
  stock.
- "Until tomorrow" and "Forever" become TastyIgniter's options. Use "Custom date & time" for a
  specific day, and "Indefinitely" for forever.

## License

MIT. See [LICENSE](LICENSE).
