<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

/**
 * "Date From / Date To" filter used on customer and user reports.
 * Defaults to the same day last month -> today (legacy behaviour).
 */
final class DateRange
{
    public function __construct(
        public readonly CarbonImmutable $from,
        public readonly CarbonImmutable $to,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $from = self::parse($request->query('from')) ?? CarbonImmutable::today()->subMonthNoOverflow();
        $to = self::parse($request->query('to')) ?? CarbonImmutable::today();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        return new self($from->startOfDay(), $to->endOfDay());
    }

    private static function parse(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return array{0:string,1:string} */
    public function bounds(): array
    {
        return [$this->from->toDateTimeString(), $this->to->toDateTimeString()];
    }

    public function query(): array
    {
        return ['from' => $this->from->toDateString(), 'to' => $this->to->toDateString()];
    }

    /** Month labels (e.g. "Jan-26") covering the range; null when longer than $maxMonths. */
    public function months(int $maxMonths = 12): ?array
    {
        $labels = [];
        $cursor = $this->from->startOfMonth();
        while ($cursor->lessThanOrEqualTo($this->to)) {
            $labels[$cursor->format('Y-m')] = $cursor->format('M-y');
            $cursor = $cursor->addMonth();
        }

        return count($labels) > $maxMonths ? null : $labels;
    }
}
