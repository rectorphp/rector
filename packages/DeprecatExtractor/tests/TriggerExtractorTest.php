<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Tests;

use Rector\Tests\AbstractContainerAwareTestCase;
use Rector\DeprecationExtractor\Deprecation\DeprecationCollector;
use Rector\DeprecationExtractor\DeprecationExtractor;

final class DeprecationExtractorTest extends AbstractContainerAwareTestCase
{
    /**
     * @var DeprecationExtractor
     */
    private $DeprecationExtractor;

    /**
     * @var DeprecationCollector
     */
    private $deprecationCollector;

    protected function setUp(): void
    {
        $this->DeprecationExtractor = $this->container->get(DeprecationExtractor::class);
        $this->deprecationCollector = $this->container->get(DeprecationCollector::class);
    }

    public function test(): void
    {
        $this->DeprecationExtractor->scanDirectories([__DIR__ . '/DeprecationExtractorSource']);
        $deprecations = $this->deprecationCollector->getDeprecations();

        $this->assertCount(2, $deprecations);

        $setClassToSetFacoryDeprecation = $deprecations[0];
        $injectMethodToTagDeprecation = $deprecations[1];

        $this->assertSame(
            'Nette\DI\Definition::setClass() second parameter $args is deprecated,'
            . ' use Nette\DI\Definition::setFactory()',
            $setClassToSetFacoryDeprecation
        );

        $this->assertSame(
            'Nette\DI\Definition::setInject() is deprecated, use Nette\DI\Definition::addTag(\'inject\')',
            $injectMethodToTagDeprecation
        );
    }
}
