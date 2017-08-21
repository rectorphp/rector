<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\NodeVisitor\ClassLikeTypeResolver;

use Nette\Utils\Html;
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

    protected function setUp(): void
    {
        $this->parser = $this->container->get(ParserInterface::class);
        $this->standaloneTraverseNodeTraverser = $this->container->get(StandaloneTraverseNodeTraverser::class);
    }

    public function test(): void
    {
        $nodes = $this->parser->parseFile(__DIR__ . '/ClassLikeTypeResolverSource/PropertyType.php');

        $nodes = $this->standaloneTraverseNodeTraverser->traverse($nodes);

        // $this->property;
        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $nodes[1]->stmts[1]->stmts[2]->stmts[0]->expr;
        $this->assertSame(Html::class, $propertyFetchNode->getAttribute('type'));

        // /** @var Type */ $property;
        $propertyNode = $nodes[1]->stmts[1]->stmts[0];
        $this->assertSame(Html::class, $propertyNode->getAttribute('type'));

        dump($propertyNode);
        die;

        // $constructorVariableNode = $nodes[1]->stmts[1]->stmts[1]->params[0]->var;
    }
}
