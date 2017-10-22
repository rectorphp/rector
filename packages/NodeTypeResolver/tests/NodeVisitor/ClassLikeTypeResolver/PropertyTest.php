<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\NodeVisitor\ClassLikeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\Contract\Parser\ParserInterface;
use Rector\Node\Attribute;
use Rector\NodeTraverser\StandaloneTraverseNodeTraverser;
use Rector\Tests\AbstractContainerAwareTestCase;

final class PropertyTest extends AbstractContainerAwareTestCase
{
    /**
     * @var ParserInterface
     */
    private $parser;

    /**
     * @var StandaloneTraverseNodeTraverser
     */
    private $standaloneTraverseNodeTraverser;

    /**
     * @var Node[]
     */
    private $nodes = [];

    protected function setUp(): void
    {
        $this->parser = $this->container->get(ParserInterface::class);
        $this->standaloneTraverseNodeTraverser = $this->container->get(StandaloneTraverseNodeTraverser::class);

        $nodes = $this->parser->parseFile(__DIR__ . '/ClassLikeTypeResolverSource/PropertyType.php');
        $this->nodes = $this->standaloneTraverseNodeTraverser->traverse($nodes);
    }

    /**
     * $this->property;.
     */
    public function testPropertyFetch(): void
    {
        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $this->nodes[1]->stmts[1]->stmts[2]->stmts[0]->expr;

        $this->assertSame('Nette\Utils\Html', $propertyFetchNode->getAttribute(Attribute::TYPE));
    }

    /**
     * $property;.
     */
    public function testProperty(): void
    {
        /** @var Node $propertyNode */
        $propertyNode = $this->nodes[1]->stmts[1]->stmts[0];
        $this->assertSame('Nette\Utils\Html', $propertyNode->getAttribute(Attribute::TYPE));
    }

    /**
     * method(Type $parameter).
     */
    public function testMethodParameter(): void
    {
        /** @var Node $constructorVariableNode */
        $constructorVariableNode = $this->nodes[1]->stmts[1]->stmts[1]->params[0]->var;
        $this->assertSame('Nette\Utils\Html', $constructorVariableNode->getAttribute(Attribute::TYPE));
    }
}
