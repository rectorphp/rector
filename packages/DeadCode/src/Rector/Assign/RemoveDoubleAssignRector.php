<?php declare(strict_types=1);

namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RemoveDoubleAssignRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Simplify useless double assigns', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$value = 1;
$value = 1;
CODE_SAMPLE
                ,
                '$value = 1;'
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        /** @var Expression|null $previousExpression */
        $previousExpression = $node->getAttribute(Attribute::PREVIOUS_EXPRESSION);
        if ($previousExpression === null) {
            return null;
        }

        if (! $previousExpression->expr instanceof Assign) {
            return null;
        }

        if (! $this->areNodesEqual($previousExpression->expr, $node)) {
            return null;
        }

        if ($node->expr instanceof FuncCall || $node->expr instanceof StaticCall || $node->expr instanceof MethodCall) {
            return null;
        }

        // skip different method expressions
        if ($node->getAttribute(Attribute::METHOD_NAME) !== $previousExpression->getAttribute(Attribute::METHOD_NAME)) {
            return null;
        }

        // are 2 different methods
        if (! $this->areNodesEqual(
            $node->getAttribute(Attribute::METHOD_NODE),
            $previousExpression->getAttribute(Attribute::METHOD_NODE)
        )) {
            return null;
        }

        // no calls on right, could hide e.g. array_pop()|array_shift()
        $this->removeNode($node);

        return $node;
    }
}
