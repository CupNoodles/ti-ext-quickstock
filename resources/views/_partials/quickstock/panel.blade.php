@if(!$location)
    <p class="p-3 mb-0 text-muted">@lang('cupnoodles.quickstock::default.text_no_locations')</p>
@elseif($categories->isEmpty())
    <p class="p-3 mb-0 text-muted">@lang('cupnoodles.quickstock::default.text_empty')</p>
@else
    {{-- Same markup as admin list widgets; the status column is core's Inventory partial --}}
    <div class="list-table">
        <table class="table table-striped table-hover mb-0 border-bottom">
            @foreach($categories as $group)
                <thead>
                <tr>
                    <th colspan="2" class="pt-4 px-3">{{ $group['name'] }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($group['menus'] as $menu)
                    {!! $this->makePartial('row', [
                        'stock' => $stocks->get('menus:'.$menu->getKey()),
                        'name' => \CupNoodles\QuickStock\Http\Controllers\QuickStock::displayName($menu, $useDocketNames),
                        'editUrl' => admin_url('menus/edit/'.$menu->getKey()),
                        'isOption' => false,
                    ]) !!}
                    @if($showOptions)
                        @foreach(\CupNoodles\QuickStock\Http\Controllers\QuickStock::optionValuesFor(collect([$menu])) as $optionValue)
                            {!! $this->makePartial('row', [
                                'stock' => $stocks->get('menu_option_values:'.$optionValue->getKey()),
                                'name' => $optionValue->value,
                                'editUrl' => null,
                                'isOption' => true,
                            ]) !!}
                        @endforeach
                    @endif
                @endforeach
                </tbody>
            @endforeach
        </table>
    </div>
@endif
