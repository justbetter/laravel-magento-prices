<?php

namespace JustBetter\MagentoPrices\Tests\Listeners;

use JustBetter\MagentoPrices\Listeners\ProductDataModifiedListener;
use JustBetter\MagentoPrices\Models\Price;
use JustBetter\MagentoPrices\Tests\TestCase;
use JustBetter\MagentoProducts\Events\ProductDataModifiedEvent;
use PHPUnit\Framework\Attributes\Test;

class ProductDataModifiedListenerTest extends TestCase
{
    #[Test]
    public function it_sets_retrieve_when_website_ids_modify(): void
    {
        $oldData = [
            'extension_attributes' => [
                'website_ids' => [1, 2, 3],
            ],
        ];
        $newData = [
            'extension_attributes' => [
                'website_ids' => [1, 2, 3, 4],
            ],
        ];

        $price = Price::query()->create([
            'sku' => '::sku::',
            'update' => false,
        ]);

        $event = new ProductDataModifiedEvent('::sku::', $oldData, $newData);

        /** @var ProductDataModifiedListener $listener */
        $listener = app(ProductDataModifiedListener::class);

        $listener->handle($event);

        $this->assertTrue($price->refresh()->update);
    }

    #[Test]
    public function it_does_not_do_anything_without_old_data(): void
    {
        $oldData = null;
        $newData = [
            'extension_attributes' => [
                'website_ids' => [1, 2, 3, 4],
            ],
        ];

        $price = Price::query()->create([
            'sku' => '::sku::',
            'update' => false,
        ]);

        $event = new ProductDataModifiedEvent('::sku::', $oldData, $newData);

        /** @var ProductDataModifiedListener $listener */
        $listener = app(ProductDataModifiedListener::class);

        $listener->handle($event);

        $this->assertFalse($price->refresh()->update);
    }
}
