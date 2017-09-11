<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Tests\Transformer;

use Rector\DeprecationExtractor\Deprecation\ClassDeprecation;
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
        $this->assertCount(17, $deprecationMessages);

        $deprecationMesssage = $deprecationMessages[0]['message'];
        $relatedNode = $deprecationMessages[0]['node'];

        $deprecation = $this->messageToDeprecationTransformer->transform(
            $deprecationMesssage,
            $relatedNode
        );

        /** @var ClassDeprecation $deprecation */
        $this->assertInstanceOf(ClassDeprecation::class, $deprecation);

        $this->assertSame('Symfony\Component\DependencyInjection\DefinitionDecorator', $deprecation->getOldClass());
        $this->assertSame('Symfony\Component\DependencyInjection\ChildDefinition', $deprecation->getNewClass());
    }
}
