<?php declare(strict_types=1);

namespace Rector\Tests\Rector;

use Rector\Exception\Rector\RectorNotFoundException;
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

        $dummyRector = $this->rectorCollector->getRector(DummyRector::class);
        $this->assertInstanceOf(DummyRector::class, $dummyRector);
    }

    public function testCounts(): void
    {
        $this->assertCount(1, $this->rectorCollector->getRectors());
        $this->assertSame(1, $this->rectorCollector->getRectorCount());
    }

    public function testGetRectors(): void
    {
        $rectors = $this->rectorCollector->getRectors();

        $this->assertArrayHasKey(DummyRector::class, $rectors);
        $this->assertInstanceOf(DummyRector::class, $rectors[DummyRector::class]);
    }

    public function testGetNonExistinsRector(): void
    {
        $this->expectException(RectorNotFoundException::class);
        $this->expectExceptionMessage(sprintf(
            'Rectors class "%s" was not found. Available rectors are: "%s".',
            'MissingRector',
            DummyRector::class
        ));

        $this->rectorCollector->getRector('MissingRector');
    }
}
