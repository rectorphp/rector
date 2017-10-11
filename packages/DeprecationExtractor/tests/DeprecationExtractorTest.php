<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Tests;

use Rector\DeprecationExtractor\Deprecation\DeprecationCollector;
use Rector\DeprecationExtractor\DeprecationExtractor;
use Rector\Tests\AbstractContainerAwareTestCase;

final class DeprecationExtractorTest extends AbstractContainerAwareTestCase
{
    /**
     * @var DeprecationCollector
     */
    private $deprecationCollector;

    protected function setUp(): void
    {
        $this->deprecationCollector = $this->container->get(DeprecationCollector::class);

        /** @var DeprecationExtractor $deprecationExtractor */
        $deprecationExtractor = $this->container->get(DeprecationExtractor::class);
        $deprecationExtractor->scanDirectories([__DIR__ . '/DeprecationExtractorSource']);
    }

    public function testDeprectaionMessages(): void
    {
        $deprecationMessages = $this->deprecationCollector->getDeprecations();
        $this->assertCount(2, $deprecationMessages);
    }
}
