<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests;

use PhpParser\Node\Expr\Variable;
use Rector\Contract\Parser\ParserInterface;
use Rector\NodeTraverser\StandaloneTraverseNodeTraverser;
use Rector\Tests\AbstractContainerAwareTestCase;

final class NodeTypeResolverTest extends AbstractContainerAwareTestCase
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
        $nodes = $this->parser->parseFile(__DIR__ . '/NodeTypeResolverSource/VariableType.php');

        $nodes = $this->standaloneTraverseNodeTraverser->traverse($nodes);

        /** @var Variable $htmlVariableNode */
        $htmlVariableNode = $nodes[1]->stmts[1]->stmts[0]->stmts[0]->expr->var;

        $this->assertSame(
            'Nette\Utils\Html',
            $htmlVariableNode->getAttribute('type')
        );
    }
}
