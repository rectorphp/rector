<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\Tests\DocBlock;

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

        $docBlock = $this->docBlockFactory->createFromNode($classNode);
        $this->assertInstanceOf(DocBlock::class, $docBlock);

        $nodes = $this->parser->parseFile(__DIR__ . '/DocBlockFactorySource/SomeClassWithReturn.php.inc');
        $methodNode = $nodes[1]->stmts[0];

        $docBlock = $this->docBlockFactory->createFromNode($methodNode);
        $this->assertInstanceOf(DocBlock::class, $docBlock);
    }
}
