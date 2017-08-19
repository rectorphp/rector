<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests;

use PhpParser\Node\Expr\Variable;
use PhpParser\NodeTraverser;
use Rector\Contract\Parser\ParserInterface;
use Rector\Tests\AbstractContainerAwareTestCase;

final class NodeTypeResolverTest extends AbstractContainerAwareTestCase
{
    /**
     * @var ParserInterface
     */
    private $parser;

    /**
     * @var NodeTraverser
     */
    private $nodeTraverser;

    protected function setUp(): void
    {
        $this->parser = $this->container->get(ParserInterface::class);
        $this->nodeTraverser = $this->container->get(NodeTraverser::class);
    }

    public function test(): void
    {
        // @todo: consider using event in parseFile() method
        $nodes = $this->parser->parseFile(__DIR__ . '/NodeTypeResolverSource/VariableType.php');

        // run basic traverser here
        $this->nodeTraverser->traverse($nodes);

        /** @var Variable $htmlVariableNode */
        $htmlVariableNode = $nodes[1]->stmts[1]->stmts[0]->stmts[0]->expr->var;

        $this->assertSame(
            'Nette\Utils\Html',
            $htmlVariableNode->getAttribute('type')
        );
    }
}
