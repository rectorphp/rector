<?php declare(strict_types=1);

namespace Rector\TriggerExtractor\Tests\Rector;

use Rector\Tests\AbstractContainerAwareTestCase;
use Rector\TriggerExtractor\Rector\RectorFactory;
use Rector\TriggerExtractor\TriggerExtractor;

final class RectorFactoryTest extends AbstractContainerAwareTestCase
{
    /**
     * @var RectorFactory
     */
    private $rectorFactory;

    protected function setUp(): void
    {
        $this->rectorFactory = $this->container->get(RectorFactory::class);

        $triggerExtractor = $this->container->get(TriggerExtractor::class);
        $triggerExtractor->scanDirectories([__DIR__ . '/../TriggerExtractorSource']);
    }

    public function test(): void
    {
        $rectors = $this->rectorFactory->createRectors();
        $this->assertCount(1, $rectors);
    }
}
