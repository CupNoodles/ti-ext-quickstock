@php
    $firstlocation = true;
@endphp


<div class="form-widget">
    <div id="form-primary-tabs" class="primary-tabs " data-control="form-tabs" data-store-name="widget.menus-form-edit">
        <div class="tab-heading">
            <ul class="form-nav nav nav-tabs">
                @foreach($locations as $key => $value)
                
                <li class="nav-item">
                    <a class="nav-link {{ $firstlocation ? 'active' : '' }} " href="#primarytab-{{ $value->location_id }}" data-toggle="tab" data-original-title="" title="">{{ $value->location_name }}</a>
                </li>
                @php
                    $firstlocation = false;
                @endphp
                @endforeach
            </ul>
</div>
@php
    $firstlocation = true;
@endphp

<div class="tab-content">
    @foreach($locations as $key => $location)
    <div class="tab-pane {{ $firstlocation ? 'active' : '' }} " id="primarytab-{{ $location->location_id }}">
            <div class="form-fields">
            @foreach ($location->categories as $key=>$category)
            <h4>{{ $category->name }}</h4>
            <br />
            <table class="table table-striped mb-0 border-bottom">
                <thead>
                    <tr>
                        <th class="list-action"></th>
                        <th class="list-cell-name-menu-name list-cell-type-text ">Item Name</th>
                        <th class="list-cell-name-menu-name list-cell-type-text ">Stock qty</th>
                        <th class="list-cell-name-menu-name list-cell-type-text ">Out of Stock</th>
                        <th class="list-cell-name-menu-name list-cell-type-text ">Status</th>  
                    </tr>
                </thead>
                <tbody>
                    @foreach($category->menus as $key=>$menu)

                    <tr>
                        
                        <td class="list-action ">
                            <a class="btn btn-edit" href="/admin/menus/edit/{{$menu->menu_id}}" data-original-title="" title="">
                            <i class="fa fa-pencil"></i>
                            </a>
                        </td>
                                                                                
                        <td class="list-col-index-1 list-col-name-menu-name list-col-type-text ">{{ $menu->menu_name }}</td>
                        <td class="list-col-index-2 list-col-name-category list-col-type-text ">{{ $menu->stock_qty }}</td>
                        <td class="list-col-index-5 list-col-name-special-status list-col-type-switch ">
                            <div class="field-custom-container">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" 
                                    name="out_of_stock" 
                                    id="out_of_stock_{{ $location->location_id }}_{{ $menu->menu_id }}" 
                                    class="custom-control-input quickstock-checkbox" 
                                    value="1"
                                    data-location="{{ $location->location_id }}"
                                    data-menu="{{ $menu->menu_id }}"
                                    {{ !$menu->out_of_stock ? 'checked="checked"' : ''}}>
                                    <label class="custom-control-label" for="out_of_stock_{{ $location->location_id }}_{{ $menu->menu_id }}">Out of Stock/In Stock</label>
                                </div>
                            </div>
                        </td>
                        <td class="list-col-index-6 list-col-name-menu-status list-col-type-switch ">
                            <div class="field-custom-container">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" 
                                    name="status" 
                                    id="status_{{ $location->location_id }}_{{ $menu->menu_id }}" 
                                    class="custom-control-input quickstock-checkbox" 
                                    value="1"
                                    data-location="{{ $location->location_id }}"
                                    data-menu="{{ $menu->menu_id }}"
                                    {{ $menu->menu_status ? 'checked="checked"' : ''}}>
                                    <label class="custom-control-label" for="status_{{ $location->location_id }}_{{ $menu->menu_id }}">Disabled/Enabled</label>
                                </div>
                            </div>
                        </td>
                        
                    </tr>
                    @endforeach
                </tbody>
    </table>














            @endforeach
            
            </div>  

    </div>
    @php
        $firstlocation = false;
    @endphp
    @endforeach
</div>