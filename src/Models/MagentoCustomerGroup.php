<?php

namespace JustBetter\MagentoPrices\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property array $data
 * @property ?Carbon $imported_at
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class MagentoCustomerGroup extends Model
{
    protected $table = 'magento_prices_customer_groups';

    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
        'imported_at' => 'datetime',
    ];
}
