<tr>
    {{-- Name column shrinks to the longest name so the controls sit next to it on wide screens --}}
    <td class="text-nowrap pe-5" style="width: 1%">
        @if($isOption)
            <span class="ps-4 text-muted" title="@lang('cupnoodles.quickstock::default.text_shared_option')">{{ $name }}</span>
        @else
            <a href="{{ $editUrl }}" class="text-reset">{{ $name }}</a>
        @endif
    </td>
    <td>
        @if($stock)
            {{-- Core's Inventory control; QuickStock::renderPanel() adjusts it for this page --}}
            @include('igniter.cart::_partials.inventory.stock_status_column', ['record' => $stock])
        @endif
    </td>
</tr>
