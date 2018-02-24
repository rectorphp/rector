<?php declare(strict_types=1);

namespace Rector\Tests\Rector;

use Rector\Rector\RectorCollector;
use Rector\Tests\AbstractContainerAwareTestCase;
use Rector\Tests\Rector\RectorCollectorSource\DummyRector;

final class RectorCollectorTest extends AbstractContainerAwareTestCase
{
    /**
     * @var RectorCollector
     */
    private $rectorCollector;

    protected function setUp(): void
    {
        $this->rectorCollector = $this->container->get(RectorCollector::class);
        $this->rectorCollector->addRector(new DummyRector());
    }

    public function testCounts(): void
    {
        $this->assertCount(2, $this->rectorCollector->getRectors());
        $this->assertSame(2, $this->rectorCollector->getRectorCount());
    }

    public function testGetRectors(): void
    {
        $rectors = $this->rectorCollector->getRectors();

        $this->assertArrayHasKey(DummyRector::class, $rectors);
        $this->assertInstanceOf(DummyRector::class, $rectors[DummyRector::class]);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
