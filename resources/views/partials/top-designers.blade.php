<div class="card h-100">
    <div class="card-header"><h2 class="card-title">Top designers</h2><span class="small text-muted">This month</span></div>
    <div class="card-body">
        @php($max = max(1, $topDesigners->max('orders') ?? 1))
        @forelse($topDesigners as $d)
            <div class="mb-3">
                <div class="d-flex justify-content-between small mb-1">
                    <span class="fw-semibold">{{ $d->name }}</span>
                    <span class="text-muted tabular">{{ number_format($d->orders) }} orders</span>
                </div>
                <div class="progress" style="height:8px; background: var(--bi-surface-2)">
                    <div class="progress-bar" style="width: {{ round($d->orders / $max * 100) }}%; background: var(--bi-primary); border-radius: 99px"></div>
                </div>
                <div class="text-muted" style="font-size:.72rem">{{ number_format($d->stitches) }} stitches</div>
            </div>
        @empty
            <div class="empty py-4"><i class="bi bi-trophy"></i><strong>No completions yet</strong><div class="small">Designer rankings appear here.</div></div>
        @endforelse
    </div>
</div>
