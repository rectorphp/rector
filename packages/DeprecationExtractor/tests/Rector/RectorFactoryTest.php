<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Tests\Rector;

use PHPUnit\Framework\Assert;
use Rector\DeprecationExtractor\DeprecationExtractor;
use Rector\DeprecationExtractor\Rector\RectorFactory;
use Rector\Rector\Dynamic\MethodNameReplacerRector;
use Rector\Tests\AbstractContainerAwareTestCase;

final class RectorFactoryTest extends AbstractContainerAwareTestCase
{
    /**
     * @var RectorFactory
     */
    private $rectorFactory;

    protected function setUp(): void
    {
        $this->rectorFactory = $this->container->get(RectorFactory::class);

        /** @var DeprecationExtractor $deprecationExtractor */
        $deprecationExtractor = $this->container->get(DeprecationExtractor::class);
        $deprecationExtractor->scanDirectories([__DIR__ . '/../DeprecationExtractorSource']);
    }

    public function test(): void
    {
        $rectors = $this->rectorFactory->createRectors();
        $this->assertCount(2, $rectors);

        /** @var MethodNameReplacerRector $secondRector */
        $secondRector = $rectors[0];
        $this->assertInstanceOf(MethodNameReplacerRector::class, $secondRector);

        $this->assertSame([
            'Nette\DI\ServiceDefinition' => [
                'setInject' => 'addTag',
            ],
        ], Assert::getObjectAttribute($secondRector, 'perClassOldToNewMethods'));

        /** @var MethodNameReplacerRector $firstRector */
        $firstRector = $rectors[1];
        $this->assertInstanceOf(MethodNameReplacerRector::class, $firstRector);

        $this->assertSame([
            'Nette\DI\ServiceDefinition' => [
                'setClass' => 'setFactory',
            ],
        ], Assert::getObjectAttribute($firstRector, 'perClassOldToNewMethods'));
    }
}
