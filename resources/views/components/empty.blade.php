@props(['icon' => 'bi-inbox', 'title' => 'Nothing here yet', 'colspan' => null])
@if($colspan)
    <tr><td colspan="{{ $colspan }}" class="p-0">
@endif
<div class="empty">
    <i class="bi {{ $icon }}"></i>
    <strong>{{ $title }}</strong>
    <div class="small">{{ $slot }}</div>
</div>
@if($colspan)
    </td></tr>
@endif
