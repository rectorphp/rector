<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Tests\Tranformer;

use Rector\DeprecationExtractor\Deprecation\DeprecationCollector;
use Rector\DeprecationExtractor\DeprecationExtractor;
use Rector\DeprecationExtractor\Transformer\MessageToDeprecationTransformer;
use Rector\Tests\AbstractContainerAwareTestCase;

final class MessageToDeprecationTransformerTest extends AbstractContainerAwareTestCase
{
    /**
     * @var DeprecationExtractor
     */
    private $deprecationExtractor;

    /**
     * @var DeprecationCollector
     */
    private $deprecationCollector;

    /**
     * @var MessageToDeprecationTransformer
     */
    private $messageToDeprecationTransformer;

    protected function setUp(): void
    {
        $this->deprecationExtractor = $this->container->get(DeprecationExtractor::class);
        $this->deprecationCollector = $this->container->get(DeprecationCollector::class);
        $this->messageToDeprecationTransformer = $this->container->get(MessageToDeprecationTransformer::class);
    }

    public function test(): void
    {
        $this->deprecationExtractor->scanDirectories([
            __DIR__ . '/../../../../vendor/symfony/dependency-injection',
        ]);

        $deprecationMessages = $this->deprecationCollector->getDeprecationMessages();
        dump($deprecationMessages);
        die;

//        $this->messageToDeprecationTransformer->transform();

        // @todo
        $this->assertSame(1, 1);
    }
}
