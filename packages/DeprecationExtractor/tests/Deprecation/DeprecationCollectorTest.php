<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Tests\Deprecation;

use Rector\DeprecationExtractor\Deprecation\ClassDeprecation;
use Rector\DeprecationExtractor\Deprecation\DeprecationCollector;
use Rector\DeprecationExtractor\DeprecationExtractor;
use Rector\Tests\AbstractContainerAwareTestCase;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

final class DeprecationCollectorTest extends AbstractContainerAwareTestCase
{
    /**
     * @var DeprecationExtractor
     */
    private $deprecationExtractor;

    /**
     * @var DeprecationCollector
     */
    private $deprecationCollector;

    protected function setUp(): void
    {
        $this->deprecationExtractor = $this->container->get(DeprecationExtractor::class);
        $this->deprecationCollector = $this->container->get(DeprecationCollector::class);
    }

    public function test(): void
    {
        $this->deprecationExtractor->scanDirectories([
            __DIR__ . '/../../../../vendor/symfony/dependency-injection',
        ]);

        $deprecations = $this->deprecationCollector->getDeprecations();
        $this->assertGreaterThanOrEqual(35, $deprecations);
    }
}
