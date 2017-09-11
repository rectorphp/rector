<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Tests;

use PhpParser\Node\Arg;
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

        $deprecationExtractor = $this->container->get(DeprecationExtractor::class);
        $deprecationExtractor->scanDirectories([__DIR__ . '/DeprecationExtractorSource']);
    }

    public function testDeprectaionMessages(): void
    {
        $deprecationMessages = $this->deprecationCollector->getDeprecationMessages();
        $this->assertCount(0 , $deprecationMessages);
    }

    public function testDeprecationNodes(): void
    {
        $deprecationArgNodes = $this->deprecationCollector->getDeprecationArgNodes();
        $this->assertCount(2, $deprecationArgNodes);

        $deprecationArgNode = $deprecationArgNodes[0];
        $this->assertInstanceOf(Arg::class, $deprecationArgNode);
    }
}
