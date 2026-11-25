@props(['label', 'value', 'icon' => 'bi-graph-up', 'tone' => 'primary', 'meta' => null])
<div {{ $attributes->class(['stat']) }}>
    <div class="stat-icon tone-{{ $tone }}"><i class="bi {{ $icon }}"></i></div>
    <div class="stat-label">{{ $label }}</div>
    <div class="stat-value">{{ $value }}</div>
    @if($meta)<div class="stat-meta">{!! $meta !!}</div>@endif
</div>
