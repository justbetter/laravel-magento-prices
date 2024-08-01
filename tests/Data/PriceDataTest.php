<?php

namespace JustBetter\MagentoPrices\Tests\Data;

use Illuminate\Validation\ValidationException;
use JustBetter\MagentoPrices\Data\PriceData;
use JustBetter\MagentoPrices\Tests\Fakes\FakeRepository;
use JustBetter\MagentoPrices\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PriceDataTest extends TestCase
{
    #[Test]
    public function it_passes_simple_rules(): void
    {
        config()->set('magento-prices.repository', PriceData::class);

        PriceData::of([
            'sku' => '::sku::',
        ]);

        $this->assertTrue(true, 'No exception thrown');
    }

    #[Test]
    public function it_fails_rules(): void
    {
        config()->set('magento-prices.repository', FakeRepository::class);

        $this->expectException(ValidationException::class);

        PriceData::of([]);
    }

    #[Test]
    public function it_calculates_checksum(): void
    {
        $data = PriceData::of([
            'sku' => '::sku::',
        ]);

        $this->assertEquals('b5a9aed3556af7b01952f7fdcd28fdd8', $data->checksum());
    }

    #[Test]
    public function it_handles_array_operations(): void
    {
        $data = PriceData::of([
            'sku' => '::sku::',
        ]);

        $data['base_prices'] = [];

        $this->assertEquals([], $data['base_prices']);
        unset($data['base_prices']);

        $this->assertNull($data['base_prices']);
    }
}
