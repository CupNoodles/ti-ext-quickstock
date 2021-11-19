<!--
This is an example of how to show a menu item's out-of-stock locationable status. The fa-plus icon will be disabled when this menu item is out of stock. 

Try using this file by replacing extensions/igniter/local/components/menu/button.blade.php with this file, or edit the component file through the admin UI.
-->
<button
    class="btn btn-light btn-sm btn-cart{{ ($menuItemObject->mealtimeIsNotAvailable || $menuItemObject->model->isOutOfStock($location->getId(), $location->orderDateTime()) ) ? ' disabled' : '' }}"
    @if (!$menuItemObject->mealtimeIsNotAvailable && !$menuItemObject->model->isOutOfStock($location->getId(), $location->orderDateTime()))
    @if ($menuItemObject->hasOptions)
    data-cart-control="load-item"
    data-menu-id="{{ $menuItem->menu_id }}"
    data-quantity="{{ $menuItem->minimum_qty }}"
    @else
    data-request="{{ $updateCartItemEventHandler }}"
    data-request-data="menuId: '{{ $menuItem->menu_id }}', quantity: '{{ $menuItem->minimum_qty }}'"
    data-replace-loading="fa fa-spinner fa-spin"
    @endif
    @elseif($menuItemObject->mealtimeIsNotAvailable)
    title="{{ implode("\r\n", $menuItemObject->mealtimeTitles) }}"
    @elseif($menuItemObject->model->isOutOfStock($location->getId(), $location->orderDateTime()))
    title="Out of stock"
    @endif
>
    <i class="fa fa-{{ $menuItemObject->mealtimeIsNotAvailable ? 'clock-o' : 'plus' }}"></i>
</button>