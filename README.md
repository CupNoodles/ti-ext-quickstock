## Quick 86

Single admin page organized by category for quickly setting items in and out of stock. 

Quick 86 creates a new locationable property that overrides a menu location's out-of-stockness. Since the property doesn't exist in the the local/menu view, users of this extension will need to implement their own menu view (an example for tastyigniter-orange is included).

Global status (enabled/disabled) of each menu item can also be edited from each location view. 

### Admin Panel
New editor view is registered at /admin/cupnoodles/quickstock/quickstock/view, under 'Restaurant' on the admin menu. 

Please note that on this view, while the 'out of stock' column is specific per location, the 'status' column is actually global for all locations. 

### Usage within a theme

By default, the extension will prevent customers from adding out-of-stock menu items to their cart. 

The extention adds a new dynamic method `isOutOfStock` to the `Menus_model` class so that individual themes can display an item's out-of-stock status. An example of how this can be done is included in `example.button.blade.php`.






