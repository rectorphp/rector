<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\NodeVisitor;

use Nette\Utils\Html;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\Contract\Parser\ParserInterface;
use Rector\NodeTraverser\StandaloneTraverseNodeTraverser;
use Rector\Tests\AbstractContainerAwareTestCase;

final class ClassLikeTypeResolverTest extends AbstractContainerAwareTestCase
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

    public function testVariable(): void
    {
        $nodes = $this->parser->parseFile(__DIR__ . '/ClassLikeTypeResolverSource/VariableType.php');

        $nodes = $this->standaloneTraverseNodeTraverser->traverse($nodes);

        /** @var Variable $htmlVariableNode */
        $htmlVariableNode = $nodes[1]->stmts[1]->stmts[0]->stmts[0]->expr->var;
        $this->assertSame(Html::class, $htmlVariableNode->getAttribute('type'));

        /** @var Variable $assignedVariableNode */
        $assignedVariableNode = $nodes[1]->stmts[1]->stmts[0]->stmts[1]->expr->var;
        $this->assertSame(Html::class, $assignedVariableNode->getAttribute('type'));
    }

    public function testProperty(): void
    {
        $nodes = $this->parser->parseFile(__DIR__ . '/ClassLikeTypeResolverSource/PropertyType.php');

        $nodes = $this->standaloneTraverseNodeTraverser->traverse($nodes);

        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $nodes[1]->stmts[1]->stmts[2]->stmts[0]->expr;
        $this->assertSame(Html::class, $propertyFetchNode->getAttribute('type'));

        // @todo test asl well
        //$propertyNode = $nodes[1]->stmts[1]->stmts[0];
        // $constructorVariableNode = $nodes[1]->stmts[1]->stmts[1]->params[0]->var;
    }
}
