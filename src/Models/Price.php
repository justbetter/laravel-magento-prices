<?php

namespace JustBetter\MagentoPrices\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use JustBetter\MagentoAsync\Concerns\HasOperations;
use JustBetter\MagentoPrices\Repository\BaseRepository;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $sku
 * @property bool $sync
 * @property ?array $base_prices
 * @property ?array $tier_prices
 * @property ?array $special_prices
 * @property bool $has_special
 * @property bool $retrieve
 * @property bool $update
 * @property ?string $checksum
 * @property ?Carbon $last_retrieved
 * @property ?Carbon $last_updated
 * @property int $fail_count
 * @property ?Carbon $last_failed
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?MagentoProduct $product
 */
class Price extends Model
{
    use HasOperations;
    use LogsActivity;

    protected $table = 'magento_prices';

    protected $casts = [
        'last_retrieved' => 'datetime',
        'last_updated' => 'datetime',
        'last_failed' => 'datetime',
        'base_prices' => 'array',
        'tier_prices' => 'array',
        'special_prices' => 'array',
        'sync' => 'boolean',
        'has_special' => 'boolean',
        'retrieve' => 'boolean',
        'update' => 'boolean',
    ];

    protected $guarded = [];

    public static function booted(): void
    {
        static::updating(function (self $model) {
            if ($model->update && $model->retrieve) {
                if (! $model->isDirty(['retrieve'])) {
                    $model->retrieve = false;
                } else {
                    $model->update = false;
                }
            }
        });
    }

    public function product(): HasOne
    {
        return $this->hasOne(MagentoProduct::class, 'sku', 'sku');
    }

    public function registerFailure(): void
    {
        $this->fail_count++;
        $this->last_failed = now();

        $shouldRetry = $this->fail_count < BaseRepository::resolve()->failLimit();
        $this->sync = $shouldRetry;

        if (! $shouldRetry) {
            $this->update = false;
            $this->retrieve = false;
            $this->fail_count = 0;
        }

        $this->save();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->logOnly(['sync', 'base_prices', 'tier_prices', 'special_prices']);
    }
}
