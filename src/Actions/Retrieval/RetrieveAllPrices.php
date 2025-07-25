<?php

namespace JustBetter\MagentoPrices\Actions\Retrieval;

use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use JustBetter\MagentoPrices\Contracts\Retrieval\RetrievesAllPrices;
use JustBetter\MagentoPrices\Jobs\Retrieval\RetrievePriceJob;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Repository\BaseRepository;

class RetrieveAllPrices implements RetrievesAllPrices
{
    public function retrieve(?Carbon $from = null, bool $defer = true): void
    {
        $repository = BaseRepository::resolve();

        if (! $defer) {
            $repository->skus($from)->each(fn (string $sku): PendingDispatch => RetrievePriceJob::dispatch($sku));

            return;
        }

        $date = now();

        $repository->skus($from)->chunk(250)->each(function (Collection $skus) use ($date): void {
            $existing = Price::query()
                ->whereIn('sku', $skus)
                ->pluck('sku');

            Price::query()
                ->whereIn('sku', $existing)
                ->update(['retrieve' => true]);

            Price::query()->insert(
                $skus
                    ->diff($existing)
                    ->values()
                    ->map(fn (string $sku): array => [
                        'sku' => $sku,
                        'retrieve' => true,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ])->toArray()
            );
        });
    }

    public static function bind(): void
    {
        app()->singleton(RetrievesAllPrices::class, static::class);
    }
}
