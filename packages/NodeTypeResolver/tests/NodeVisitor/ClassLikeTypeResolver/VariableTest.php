<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\NodeVisitor\ClassLikeTypeResolver;

use Nette\Utils\Html;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use Rector\Contract\Parser\ParserInterface;
use Rector\Node\Attribute;
use Rector\NodeTraverser\StandaloneTraverseNodeTraverser;
use Rector\Tests\AbstractContainerAwareTestCase;

final class VariableTest extends AbstractContainerAwareTestCase
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

        $nodes = $this->parser->parseFile(__DIR__ . '/ClassLikeTypeResolverSource/VariableType.php');
        $this->nodes = $this->standaloneTraverseNodeTraverser->traverse($nodes);
    }

    /**
     * $variable.
     */
    public function testVariable(): void
    {
        /** @var Variable $htmlVariableNode */
        $htmlVariableNode = $this->nodes[1]->stmts[1]->stmts[0]->stmts[0]->expr->var;
        $this->assertSame(Html::class, $htmlVariableNode->getAttribute(Attribute::TYPE));
    }

    /**
     * $newVariable = $variable;.
     */
    public function testReassignedVariable(): void
    {
        /** @var Variable $assignedVariableNode */
        $assignedVariableNode = $this->nodes[1]->stmts[1]->stmts[0]->stmts[1]->expr->var;
        $this->assertSame(Html::class, $assignedVariableNode->getAttribute(Attribute::TYPE));
    }
}
