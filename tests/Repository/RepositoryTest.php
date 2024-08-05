<?php

namespace JustBetter\MagentoPrices\Tests\Repository;

use JustBetter\MagentoPrices\Exceptions\NotImplementedException;
use JustBetter\MagentoPrices\Repository\BaseRepository;
use JustBetter\MagentoPrices\Repository\Repository;
use JustBetter\MagentoPrices\Tests\Fakes\FakeRepository;
use JustBetter\MagentoPrices\Tests\TestCase;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use PHPUnit\Framework\Attributes\Test;

class RepositoryTest extends TestCase
{
    #[Test]
    public function getters(): void
    {
        /** @var Repository $repository */
        $repository = app(Repository::class);

        $this->assertEquals(250, $repository->retrieveLimit());
        $this->assertEquals(250, $repository->updateLimit());
        $this->assertEquals(3, $repository->failLimit());
    }

    #[Test]
    public function it_resolves_repository(): void
    {
        config()->set('magento-prices.repository', FakeRepository::class);

        $resolved = BaseRepository::resolve();

        $this->assertInstanceOf(FakeRepository::class, $resolved);
    }

    #[Test]
    public function it_throws_exception(): void
    {
        $repository = BaseRepository::resolve();

        $this->expectException(NotImplementedException::class);

        $repository->retrieve('::sku::');
    }

    #[Test]
    public function it_retrieve_magento_skus(): void
    {
        MagentoProduct::query()->create(['sku' => '::sku_1::', 'exists_in_magento' => true]);
        MagentoProduct::query()->create(['sku' => '::sku_2::', 'exists_in_magento' => false]);
        MagentoProduct::query()->create(['sku' => '::sku_3::', 'exists_in_magento' => true]);

        $repository = BaseRepository::resolve();

        $this->assertEquals(['::sku_1::', '::sku_3::'], $repository->skus()->toArray());
    }
}
