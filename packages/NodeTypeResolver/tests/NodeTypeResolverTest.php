<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests;

use PhpParser\Node\Expr\Variable;
use PhpParser\NodeTraverser;
use Rector\Contract\Parser\ParserInterface;
use Rector\NodeTypeResolver\NodeTraverser\TypeDetectingNodeTraverser;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Tests\AbstractContainerAwareTestCase;

final class NodeTypeResolverTest extends AbstractContainerAwareTestCase
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var ParserInterface
     */
    private $parser;

    /**
     * @var TypeDetectingNodeTraverser
     */
    private $typeDetectingNodeTraverser;

    /**
     * @var NodeTraverser
     */
    private $nodeTraverser;

    protected function setUp(): void
    {
        $this->nodeTypeResolver = $this->container->get(NodeTypeResolver::class);
        $this->parser = $this->container->get(ParserInterface::class);
        $this->typeDetectingNodeTraverser = $this->container->get(TypeDetectingNodeTraverser::class);
        $this->nodeTraverser = $this->container->get(NodeTraverser::class);
    }

    public function test(): void
    {
        $nodes = $this->parser->parseFile(__DIR__ . '/NodeTypeResolverSource/VariableType.php');

        // run basic traverser here
        $this->nodeTraverser->traverse($nodes);
        $this->typeDetectingNodeTraverser->traverse($nodes);

        /** @var Variable $htmlVariableNode */
        $htmlVariableNode = $nodes[1]->stmts[1]->stmts[0]->stmts[0]->expr->var;

        $this->assertSame(
            'Nette\Utils\Html',
            $htmlVariableNode->getAttribute('type')
        );
    }
}
