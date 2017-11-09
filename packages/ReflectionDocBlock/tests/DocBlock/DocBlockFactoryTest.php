<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\Tests\DocBlock;

use InvalidArgumentException;
use phpDocumentor\Reflection\DocBlock;
use PhpParser\Node\Scalar\String_;
use Rector\Parser\Parser;
use Rector\ReflectionDocBlock\DocBlock\DocBlockFactory;
use Rector\Tests\AbstractContainerAwareTestCase;

final class DocBlockFactoryTest extends AbstractContainerAwareTestCase
{
    /**
     * @var DocBlockFactory
     */
    private $docBlockFactory;

    /**
     * @var Parser
     */
    private $parser;

    protected function setUp(): void
    {
        $this->docBlockFactory = $this->container->get(DocBlockFactory::class);
        $this->parser = $this->container->get(Parser::class);
    }

    public function test(): void
    {
        $node = new String_('string');
        $docBlock = $this->docBlockFactory->createFromNode($node);
        $this->assertInstanceOf(DocBlock::class, $docBlock);
    }

    public function testMailformedAnnotations(): void
    {
        $nodes = $this->parser->parseFile(__DIR__ . '/DocBlockFactorySource/SomeClassWithAuthor.php.inc');
        $classNode = $nodes[1];

        // should be ok
        $this->expectException(InvalidArgumentException::class);
        $this->docBlockFactory->createFromNode($classNode);
    }
}
