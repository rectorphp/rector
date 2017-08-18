<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests;

use PhpParser\Parser;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Tests\AbstractContainerAwareTestCase;

final class NodeTypeResolverTest extends AbstractContainerAwareTestCase
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var Parser
     */
    private $parser;

    protected function setUp(): void
    {
        $this->nodeTypeResolver = $this->container->get(NodeTypeResolver::class);
        $this->parser = $this->container->get(Parser::class);
    }

    public function test(): void
    {
        $code = file_get_contents(__DIR__ . '/NodeTypeResolverSource/VariableType.php');
        $nodes = $this->parser->parse($code);

        $variableNode = $nodes[1]->stmts[1]->stmts[0]->stmts[0]->expr->var;
        $resolvedType = $this->nodeTypeResolver->getTypeForNode($variableNode, $nodes);
        $this->assertSame('Nette\Utils\Html', $resolvedType);

//        $assignedVariableNode = $nodes[1]->stmts[1]->stmts[0]->stmts[2]->expr;
//        $resolvedType = $this->nodeTypeResolver->getTypeForNode($assignedVariableNode, $nodes);
//        $this->assertSame('Nette\Utils\Html', $resolvedType);
    }
}
