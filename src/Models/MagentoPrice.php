<?php

namespace JustBetter\MagentoPrices\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use JustBetter\ErrorLogger\Traits\HasErrors;
use JustBetter\MagentoPrices\Data\BasePriceData;
use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Data\SpecialPriceData;
use JustBetter\MagentoPrices\Data\TierPriceData;
use JustBetter\MagentoPrices\Helpers\MoneyHelper;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $sku
 * @property bool $sync
 * @property ?array $base_prices
 * @property ?array $tier_prices
 * @property bool $has_tier
 * @property ?array $special_prices
 * @property bool $has_special
 * @property bool $retrieve
 * @property bool $update
 * @property ?Carbon $last_retrieved
 * @property ?Carbon $last_updated
 * @property int $fail_count
 * @property ?Carbon $last_failed
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class MagentoPrice extends Model
{
    use HasErrors;
    use LogsActivity;

    public $casts = [
        'last_retrieved' => 'datetime',
        'last_updated' => 'datetime',
        'last_failed' => 'datetime',
        'base_prices' => 'array',
        'tier_prices' => 'array',
        'special_prices' => 'array',
        'sync' => 'boolean',
        'has_tier' => 'boolean',
        'has_special' => 'boolean',
        'retrieve' => 'boolean',
        'update' => 'boolean',
    ];

    protected $guarded = [];

    public function scopeShouldRetrieve(Builder $builder): Builder
    {
        return $builder
            ->where('sync', true)
            ->where('retrieve', true);
    }

    public function scopeShouldUpdate(Builder $builder): Builder
    {
        return $builder
            ->where('sync', true)
            ->where('update', true);
    }

    public function getBasePricesAttribute(?string $value): Collection
    {
        /** @var MoneyHelper $helper */
        $helper = app(MoneyHelper::class);

        $prices = json_decode($value, true) ?? [];

        if (array_key_exists('price', $prices)) {
            $prices = [$prices];
        }

        return collect($prices)
            ->map(fn ($p) => new BasePriceData(
                $helper->getMoney($p['price']),
                $p['storeId']
            ));
    }

    public function getTierPricesAttribute(?string $value): Collection
    {
        /** @var MoneyHelper $helper */
        $helper = app(MoneyHelper::class);

        $prices = json_decode($value, true) ?? [];

        if (array_key_exists('price', $prices)) {
            $prices = [$prices];
        }

        return collect($prices)->map(fn ($p) => new TierPriceData(
            $p['customer_group'],
            $helper->getMoney($p['price']),
            $p['quantity'],
            $p['storeId'] ?? 0,
            $p['priceType'] ?? 'fixed',
        ));
    }

    public function getSpecialPricesAttribute(?string $value): Collection
    {
        /** @var MoneyHelper $helper */
        $helper = app(MoneyHelper::class);

        $prices = json_decode($value, true) ?? [];

        if (array_key_exists('price', $prices)) {
            $prices = [$prices];
        }

        return collect($prices)->map(function ($p) use ($helper) {
            $from = blank($p['price_from'])
                ? now()
                : Carbon::createFromFormat('Y-m-d H:i:s', $p['price_from']);

            $to = blank($p['price_to'])
                ? now()
                : Carbon::createFromFormat('Y-m-d H:i:s', $p['price_to']);

            return new SpecialPriceData(
                $helper->getMoney($p['price']),
                $p['storeId'],
                $from,
                $to
            );
        });
    }

    public function getData(): PriceData
    {
        return new PriceData(
            $this->sku,
            $this->base_prices,
            $this->tier_prices,
            $this->special_prices
        );
    }

    public function registerError(): void
    {
        $this->fail_count++;
        $this->last_failed = now();

        if ($this->fail_count > config('magento-prices.fail_count', 5)) {
            $this->update = false;
            $this->retrieve = false;
            $this->fail_count = 0;
        }

        $this->save();
    }

    public static function findBySku(string $sku): ?static
    {
        /** @var ?static $result */
        $result = static::query()
            ->where('sku', $sku)
            ->first();

        return $result;
    }

    public function specialPriceChanged(): bool
    {
        if (! $this->isDirty('special_prices')) {
            return false;
        }

        $original = collect($this->getOriginal('special_prices'));
        $updated = $this->getAttribute('special_prices');

        if (count($updated) === 0 && $original->isNotEmpty()) {
            return true;
        }

        /** @var SpecialPriceData $updatedSpecialPrice */
        foreach ($updated as $updatedSpecialPrice) {
            /** @var ?SpecialPriceData $originalPrice */
            $originalPrice = $original->where('storeId', $updatedSpecialPrice->storeId)->first();

            // Check if a new special price was added
            if ($originalPrice === null) {
                return true;
            }

            // Check if price still is in the date range
            $dateValid = $originalPrice->from->lessThan(now()) && $originalPrice->to->greaterThan(now());

            if (! $dateValid) {
                return true;
            }

            // Check if the price has changed
            if ($originalPrice->price->compareTo($updatedSpecialPrice->price) !== 0) {
                return true;
            }
        }

        return false;
    }

    public function activity(): MorphMany
    {
        return $this->morphMany(
            ActivitylogServiceProvider::determineActivityModel(),
            'subject'
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->logOnly(['sync', 'base_prices', 'tier_prices', 'special_prices']);
    }
}
