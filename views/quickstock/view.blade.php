<style>
body{
    margin-bottom: 0;
}
</style>
<div class="form-widget">
    <div id="form-primary-tabs" class="primary-tabs " data-control="form-tabs" data-store-name="widget.menus-form-edit">
        <div class="tab-heading">
            <ul class="form-nav nav nav-tabs">
                @foreach($locations as $key => $value)
                    <li class="nav-item">
                        <a class="nav-link {{ ($value->active) ? 'active' : '' }}" href="#location-{{ $value->location_id }}" data-toggle="tab" data-original-title="" title="" 
                        >{{ $value->location_name }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<div class="tab-content">

    @foreach($locations as $key => $location)
        <div class="tab-pane  {{ ($location->active) ? 'active' : '' }}" id="location-{{ $location->location_id }}">
                <div class="form-fields">
                @foreach ($location->categories as $key=>$category)
                <h4>{{ $category->name }}</h4>
                <br />
                <table class="table table-striped mb-0 border-bottom">
                    <thead>
                        <tr>
                            <th class="list-action" style="width: 5%"></th>
                            <th class="list-cell-name-menu-name list-cell-type-text " style="width:45%">Item Name</th>
                            <th class="list-cell-name-menu-name list-cell-type-text " style="width:25%">Out of Stock</th>
                            <th class="list-cell-name-menu-name list-cell-type-text " style="width:25%">Until</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($category->menus as $key=>$menu)
                            @if($menu->menu_status)
                                <tr>
                                    
                                    <td class="list-action ">
                                        <a class="btn btn-edit" href="/admin/menus/edit/{{$menu->menu_id}}" data-original-title="" title="">
                                        <i class="fa fa-pencil"></i>
                                        </a>
                                    </td>
                                                                                            
                                    <td class="list-col-index-1 list-col-name-menu-name list-col-type-text ">{{ CupNoodles\QuickStock\Models\QuickStockSettings::get('print_docket_names') && $menu->print_docket ? $menu->print_docket : $menu->menu_name }}</td>
                                    <td class="list-col-index-5 list-col-name-special-status list-col-type-switch ">
                                        <div class="field-custom-container">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" 
                                                name="out_of_stock" 
                                                id="out_of_stock_{{ $location->location_id }}_{{ $menu->menu_id }}" 
                                                class="custom-control-input quickstock-menu-checkbox" 
                                                value="1"
                                                data-location="{{ $location->location_id }}"
                                                data-menu="{{ $menu->menu_id }}"
                                                {{ !$menu->out_of_stock ? 'checked="checked"' : ''}}>
                                                <label class="custom-control-label" for="out_of_stock_{{ $location->location_id }}_{{ $menu->menu_id }}">@lang('cupnoodles.quickstock::default.out_of_stock')/@lang('cupnoodles.quickstock::default.in_stock')</label>
                                            </div>
                                        </div>
                                    </td>
                                    <td>

                                    <div id="until_{{ $location->location_id }}_{{ $menu->menu_id }}_container" {!! $menu->out_of_stock ? '' : 'style="display: none"' !!} >
                                        <button id="date_button_{{ $location->location_id }}_{{ $menu->menu_id }}" class="btn font-weight-bold p-0 dropdown-toggle text-secondary" type="button" data-toggle="dropdown">{{$menu->in_stock_date_text}}</button>
                                        <div class="dropdown-menu dropdown" aria-labelledby="dropdownMenuButton">
                                            <a  class="dropdown-item in_stock_date_action" 
                                                data-datepicker-value="{{ date(lang('cupnoodles.quickstock::default.date_format_php'), strtotime("+1 day"))}}"
                                                data-location="{{ $location->location_id }}"
                                                data-menu="{{ $menu->menu_id }}">
                                                @lang('cupnoodles.quickstock::default.tomorrow')
                                            </a>
                                            <a  class="dropdown-item in_stock_date_action"
                                                data-datepicker-value=""
                                                data-location="{{ $location->location_id }}"
                                                data-menu="{{ $menu->menu_id }}">@lang('cupnoodles.quickstock::default.forever')
                                            </a>
                                            <div class="input-group date" data-provide="datepicker-inline">
                                                <input type="hidden" 
                                                class="in_stock_date_action"
                                                name="back_in_stock_date" 
                                                id="{{ $location->location_id }}_{{ $menu->menu_id }}_back_in_stock_date"
                                                value="{{ $menu->in_stock_date }}" 
                                                data-location="{{ $location->location_id }}"
                                                data-menu="{{ $menu->menu_id }}"
                                                data-datepicker-value="{{ $menu->in_stock_date }}"
                                                data-date-format="{{ lang('cupnoodles.quickstock::default.date_format_js') }}">
                                            </div>
                                        </div>
                                    </div>



                                    </td>
                                </tr>
                            @endif
                            @if( CupNoodles\QuickStock\Models\QuickStockSettings::get('quickstock_options') && count($menu['menu_options']) )
                                @foreach($menu['menu_options'] as $option)
                                    @foreach($option['option_values'] as $option_value)
                                        <tr>
                                            <td class="list-action ">
                                            </td>
                                            <td class="list-col-index-1 list-col-name-menu-option-name list-col-type-text ">
                                                --- {{ $option->option_name }} --- {{ $option_value->value}}
                                            </td>
                                            <td class="list-col-index-5 list-col-name-special-status list-col-type-switch ">
                                                <div class="field-custom-container">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" 
                                                        name="out_of_stock" 
                                                        id="out_of_stock_{{ $location->location_id }}_{{ $menu->menu_id }}_{{ $option->option_id }}_{{ $option_value->option_value_id }}" 
                                                        class="custom-control-input quickstock-option-checkbox out_of_stock_option_value_{{ $location->location_id }}_{{ $option_value->option_value_id }}" 
                                                        value="1"
                                                        data-location="{{ $location->location_id }}"
                                                        data-menu="{{ $menu->menu_id }}"
                                                        data-option="{{ $option->option_id }}"
                                                        data-option-value="{{ $option_value->option_value_id }}"
                                                        {{ !$option_value->out_of_stock ? 'checked="checked"' : ''}}>
                                                        <label class="custom-control-label" for="out_of_stock_{{ $location->location_id }}_{{ $menu->menu_id }}_{{ $option->option_id }}_{{ $option_value->option_value_id }}">@lang('cupnoodles.quickstock::default.out_of_stock')/@lang('cupnoodles.quickstock::default.in_stock')</label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>

                                            <div 
                                            id="until_{{ $location->location_id }}_{{ $menu->menu_id }}_{{ $option->option_id }}_{{ $option_value->option_value_id }}_container"
                                            class="until_{{ $location->location_id }}_{{ $option_value->option_value_id }}_container"
                                            {!! $option_value->out_of_stock ? '' : 'style="display: none"' !!} >
                                                <button 
                                                id="date_button_{{ $location->location_id }}_{{ $menu->menu_id }}_{{ $option->option_id }}_{{ $option_value->option_value_id }}" 
                                                class="btn font-weight-bold p-0 dropdown-toggle text-secondary date_button_option_value_{{ $location->location_id }}_{{ $option_value->option_value_id }}" type="button" data-toggle="dropdown">{{$option_value->in_stock_date_text}}</button>
                                                <div class="dropdown-menu dropdown" aria-labelledby="dropdownMenuButton">
                                                    <a  class="dropdown-item in_stock_date_action" 
                                                        data-datepicker-value="{{ date(lang('cupnoodles.quickstock::default.date_format_php'), strtotime("+1 day"))}}"
                                                        data-location="{{ $location->location_id }}"
                                                        data-menu="{{ $menu->menu_id }}"
                                                        data-option="{{ $option->option_id }}"
                                                        data-option-value="{{ $option_value->option_value_id }}"
                                                        >
                                                        @lang('cupnoodles.quickstock::default.tomorrow')
                                                    </a>
                                                    <a  class="dropdown-item in_stock_date_action"
                                                        data-datepicker-value=""
                                                        data-location="{{ $location->location_id }}"
                                                        data-menu="{{ $menu->menu_id }}""
                                                        data-option="{{ $option->option_id }}"
                                                        data-option-value="{{ $option_value->option_value_id }}"
                                                        >@lang('cupnoodles.quickstock::default.forever')
                                                    </a>
                                                    <div class="input-group date" data-provide="datepicker-inline">
                                                        <input type="hidden" 
                                                        class="in_stock_date_action"
                                                        name="back_in_stock_date" 
                                                        id="{{ $location->location_id }}_{{ $menu->menu_id }}_{{ $option->option_id }}_{{ $option_value->option_value_id }}_back_in_stock_date"
                                                        value="{{ $menu->in_stock_date }}" 
                                                        data-location="{{ $location->location_id }}"
                                                        data-menu="{{ $menu->menu_id }}"
                                                        data-option="{{ $option->option_id }}"
                                                        data-option-value="{{ $option_value->option_value_id }}"
                                                        data-datepicker-value="{{ $menu->in_stock_date }}"
                                                        data-date-format="{{ lang('cupnoodles.quickstock::default.date_format_js') }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @endif
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        </div>  
    </div>
    @endforeach
</div>