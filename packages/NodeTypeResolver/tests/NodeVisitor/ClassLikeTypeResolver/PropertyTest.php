<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\NodeVisitor\ClassLikeTypeResolver;

use Nette\Utils\Html;
use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\Contract\Parser\ParserInterface;
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
        $this->assertSame(Html::class, $propertyFetchNode->getAttribute('type'));
    }

    /**
     * $property;.
     */
    public function testProperty(): void
    {
        $propertyNode = $this->nodes[1]->stmts[1]->stmts[0];
        $this->assertSame(Html::class, $propertyNode->getAttribute('type'));
    }

    /**
     * method(Type $parameter).
     */
    public function testMethodParameter(): void
    {
        $constructorVariableNode = $this->nodes[1]->stmts[1]->stmts[1]->params[0]->var;
        $this->assertSame(Html::class, $constructorVariableNode->getAttribute('type'));
    }
}
