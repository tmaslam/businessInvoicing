@props(['items'])
@if($items->hasPages() || $items->total() > 0)
    <div class="card-pager d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="small text-muted">
            Showing <strong>{{ number_format($items->firstItem() ?? 0) }}–{{ number_format($items->lastItem() ?? 0) }}</strong>
            of <strong>{{ number_format($items->total()) }}</strong>
        </span>
        @if($items->hasPages())
            {{ $items->onEachSide(1)->links() }}
        @endif
    </div>
@endif
