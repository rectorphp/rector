<?php declare(strict_types=1);

namespace Rector\TriggerExtractor\Tests\Rector;

use PHPUnit\Framework\Assert;
use Rector\Tests\AbstractContainerAwareTestCase;
use Rector\TriggerExtractor\Rector\ConfigurableChangeMethodNameRector;
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
        $this->assertCount(2, $rectors);

        /** @var ConfigurableChangeMethodNameRector $firstRector */
        $firstRector = $rectors[0];
        $this->assertInstanceOf(ConfigurableChangeMethodNameRector::class, $firstRector);

        $this->assertSame([
            'Nette\DI\Definition' => [
                'setClass' => 'setFactory',
            ],
        ], Assert::getObjectAttribute($firstRector, 'perClassOldToNewMethod'));

        /** @var ConfigurableChangeMethodNameRector $secondRector */
        $secondRector = $rectors[1];
        $this->assertInstanceOf(ConfigurableChangeMethodNameRector::class, $secondRector);

        $this->assertSame([
            'Nette\DI\Definition' => [
                'setInject' => 'addTag',
            ],
        ], Assert::getObjectAttribute($secondRector, 'perClassOldToNewMethod'));
    }
}
