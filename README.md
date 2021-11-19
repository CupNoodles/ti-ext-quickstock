## Quick 86

Single admin page organized by category for quickly setting items in and out of stock, with a new Location-specific 'out of stock' flag (not the menu item status). 

Quick 86 creates a new locationable property that overrides a menu location's out-of-stockness. Since the property doesn't exist in the the local/menu view, users of this extension will need to implement their own menu view (an example for tastyigniter-orange is included).

Please note that there's another, free extension at https://tastyigniter.com/marketplace/item/thoughtco-outofstock which serves a very similar purpose, and does *not* require any extra developer time. If you're not planning on integrating per-location out of stock dates into your TI template by hand, it's likely that you will be better served by `thoughtco-outofstock`.

Back-in-stock date can be updated from the same page, and will automatically become back in stock at the specific date. Pre-orders will also be available beyond the back-in-stock date, if the location allows pre-orders beyond the date that's been set. 

### Installation

Clone this repo into `extensions/cupnoodles/quickstock` and hit the play button under 'Extensions'. 


### Admin Panel
New editor view is registered at /admin/cupnoodles/quickstock/quickstock/view, under 'Restaurant' on the admin menu. 

Please note that on this view, while the 'out of stock' column is specific per location.

### Usage within a theme

By default, the extension will prevent customers from adding out-of-stock menu items to their cart. 

The extention adds a new dynamic method `isOutOfStock` to the `Menus_model` class so that individual themes can display an item's out-of-stock status. An example of how this can be done is included in `example.button.blade.php`.






