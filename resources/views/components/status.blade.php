@props(['status'])
@php
    $tone = match ($status) {
        'NEW' => 'primary',
        'In Progress' => 'warning',
        'Completed' => 'success',
        'Lock' => 'neutral',
        'Archive' => 'info',
        default => 'neutral',
    };
    $label = match ($status) {
        'Archive' => 'Paid',
        'Lock' => 'Locked',
        'NEW', '', null => 'New',
        default => $status,
    };
@endphp
<span {{ $attributes->class(['pill', "tone-$tone"]) }}>{{ $label }}</span>
