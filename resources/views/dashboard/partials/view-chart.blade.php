<div class="card card-body mb-4" id="property-views-chart">
    <h5 class="mb-3">Page Insight</h5>
    <form class="d-flex justify-content-between flex-wrap gap-3 mb-4" method="get" action="{{ route('dashboard') }}#property-views-chart">
        @foreach(request()->except(['period','chart_from','chart_to','page']) as $key=>$value)@if(is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif @endforeach
        <div class="d-flex gap-3">@foreach(['day'=>'Day','week'=>'Week','month'=>'Month','year'=>'Year'] as $value=>$label)<a @class(['dates','active'=>$period===$value]) href="{{ route('dashboard', array_merge(request()->except(['period','chart_from','chart_to','page']),['period'=>$value])) }}#property-views-chart" @if($period===$value) aria-current="true" @endif>{{ $label }}</a>@endforeach</div>
        <input type="hidden" name="period" value="{{ $period }}">
        <div class="d-flex flex-wrap gap-2"><label>From Date<input class="form-control" type="date" name="chart_from" value="{{ $chartFrom->toDateString() }}" max="{{ today()->toDateString() }}" data-dashboard-auto-submit></label><label>To Date<input class="form-control" type="date" name="chart_to" value="{{ $chartTo->toDateString() }}" max="{{ today()->toDateString() }}" data-dashboard-auto-submit></label></div>
    </form>
    @php
        $chartMaximum=max(4,(int)ceil($days->max('total')/4)*4);
        $chartPoints=$days->map(fn($day,$index)=>['x'=>55+$index*805/max(1,$days->count()-1),'y'=>245-$day['total']/$chartMaximum*200,'label'=>$day['day'],'total'=>$day['total']]);
        $chartLine=$chartPoints->map(fn($point)=>round($point['x'],2).','.round($point['y'],2))->implode(' ');
        $chartLabelInterval=max(1,(int)ceil($days->count()/7));
    @endphp
    <div class="chart-box"><svg viewBox="0 0 900 300" role="img" aria-label="Property views chart: {{ number_format($days->sum('total')) }} recorded views in the selected period">
        <defs><linearGradient id="views-gradient" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#EB6753" stop-opacity=".18"/><stop offset="100%" stop-color="#EB6753" stop-opacity="0"/></linearGradient></defs>
        @for($line=0;$line<=4;$line++)<line x1="55" x2="860" y1="{{ 45+$line*50 }}" y2="{{ 45+$line*50 }}" stroke="#e9edf2" stroke-dasharray="4 4"/><text x="43" y="{{ 49+$line*50 }}" text-anchor="end" class="chart-label">{{ (int)ceil($chartMaximum*(4-$line)/4) }}</text>@endfor
        <polygon points="55,245 {{ $chartLine }} {{ $chartPoints->last()['x'] }},245" fill="url(#views-gradient)"/>
        <polyline points="{{ $chartLine }}" fill="none" stroke="#EB6753" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        @foreach($chartPoints as $index=>$point)
            @if($chartPoints->count()<=32)<circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="3" fill="#EB6753"><title>{{ $point['label'] }}: {{ $point['total'] }} views</title></circle>@endif
            @if($index%$chartLabelInterval===0 || $loop->last)<text x="{{ $point['x'] }}" y="275" text-anchor="middle" class="chart-label">{{ $point['label'] }}</text>@endif
        @endforeach
    </svg></div>
    <p class="fs-14 mt-2 mb-0">{{ number_format($days->sum('total')) }} views in this period · {{ number_format($counts['Property views']) }} total property views. One view per session, property, and day; owner previews are excluded.</p>
</div>
