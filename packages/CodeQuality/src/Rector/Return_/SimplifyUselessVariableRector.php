<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Return_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeAnalyzer\AssignToBinaryMap;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see Based on https://github.com/slevomat/coding-standard/blob/master/SlevomatCodingStandard/Sniffs/Variables/UselessVariableSniff.php
 */
final class SimplifyUselessVariableRector extends AbstractRector
{
    /**
     * @var AssignToBinaryMap
     */
    private $assignToBinaryMap;

    public function __construct(AssignToBinaryMap $assignToBinaryMap)
    {
        $this->assignToBinaryMap = $assignToBinaryMap;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes useless variable assigns', [
            new CodeSample(
                <<<'CODE_SAMPLE'
function () {
    $a = true;
    return $a;
};
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
function () {
    return true;
};
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Return_::class];
    }

    /**
     * @param Return_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var AssignOp|Assign $previousNode */
        $previousNode = $node->getAttribute(Attribute::PREVIOUS_NODE)->expr;

        $previousVariableNode = $previousNode->var;

        // has some comment
        if ($previousVariableNode->getComments() || $previousVariableNode->getDocComment()) {
            return null;
        }

        if ($previousNode instanceof Assign) {
            $node->expr = $previousNode->expr;
        }

        if ($previousNode instanceof AssignOp) {
            $binaryClass = $this->assignToBinaryMap->getAlternative($previousNode);
            if (! $binaryClass) {
                return null;
            }

            $node->expr = new $binaryClass($previousNode->var, $previousNode->expr);
        }

        $this->removeNode($previousNode);

        return $node;
    }

    private function shouldSkip(Return_ $returnNode): bool
    {
        if (! $returnNode->expr instanceof Variable) {
            return true;
        }

        $variableNode = $returnNode->expr;

        $previousExpression = $returnNode->getAttribute(Attribute::PREVIOUS_NODE);
        if ($previousExpression === null || ! $previousExpression instanceof Expression) {
            return true;
        }

        // is variable part of single assign
        $previousNode = $previousExpression->expr;
        if (! $previousNode instanceof AssignOp && ! $previousNode instanceof Assign) {
            return true;
        }

        // is the same variable
        return ! $this->areNodesEqual($previousNode->var, $variableNode);
    }
}
