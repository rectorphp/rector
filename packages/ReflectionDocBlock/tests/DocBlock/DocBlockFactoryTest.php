<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\DocBlock;

use phpDocumentor\Reflection\DocBlock;
use PhpParser\Node\Scalar\String_;
use Rector\Tests\AbstractContainerAwareTestCase;

final class DocBlockFactoryTest extends AbstractContainerAwareTestCase
{
    /**
     * @var DocBlockFactory
     */
    private $docBlockFactory;

    protected function setUp(): void
    {
        $this->docBlockFactory = $this->container->get(DocBlockFactory::class);
    }

    public function test(): void
    {
        $node = new String_('string');
        $docBlock = $this->docBlockFactory->createFromNode($node);
        $this->assertInstanceOf(DocBlock::class, $docBlock);
    }
}
