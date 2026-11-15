<div class="d-flex p-3">
    <h4 class="page-title mb-0 lh-base">
        <span>{!! Template::getHeading() !!}</span>
    </h4>
</div>

<div class="row-fluid">
    <div class="card shadow-sm mx-3 mb-3">
        @if($locations->count() > 1)
            <div class="border-bottom px-3 pt-2">
                <ul class="nav nav-tabs border-bottom-0">
                    @foreach($locations as $tabLocation)
                        <li class="nav-item">
                            <a
                                @class(['nav-link', 'active' => $location?->getKey() === $tabLocation->getKey()])
                                href="{{ admin_url('cupnoodles/quickstock/quick_stock', ['location' => $tabLocation->getKey()]) }}"
                            >{{ $tabLocation->location_name }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div id="quickstock-panel">
            {!! $this->renderPanel() !!}
        </div>
    </div>
</div>

<style>
    /* Core's stock status badge uses Bootstrap's small .badge size; make it readable here */
    #quickstock-panel .badge {
        font-size: .875rem;
    }

    /* The admin theme doesn't shade .table-striped rows */
    #quickstock-panel .table-striped > tbody > tr:nth-of-type(odd) > * {
        background-color: rgba(0, 0, 0, .035);
    }
</style>
