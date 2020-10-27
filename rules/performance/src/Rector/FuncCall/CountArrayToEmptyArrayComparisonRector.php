<?php

declare(strict_types=1);

namespace Rector\Performance\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\LNumber;
use PHPStan\Type\ArrayType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\CountArrayToEmptyArrayComparisonRectorTest
 */
final class CountArrayToEmptyArrayComparisonRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change count array comparison to empty array comparison to improve performance', [
            new CodeSample(
                <<<'CODE_SAMPLE'
count($array) === 0;
count($array) > 0;
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
$array === [];
$array !== [];
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->name instanceof FullyQualified) {
            return null;
        }

        $functionName = $this->getName($node);
        if ($functionName === null) {
            return null;
        }

        if ($functionName !== 'count') {
            return null;
        }

        $args = $node->args;
        $variable = $args[0]->value;
        $scope = $variable->getAttribute(AttributeKey::SCOPE);
        $type = $scope->getType($variable);

        // no pass array type, skip
        if (! $type instanceof ArrayType) {
            return null;
        }

        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);

        if (! $parent instanceof Identical && ! $parent instanceof Greater && ! $parent instanceof Smaller) {
            return null;
        }

        $compareVariable = new Variable($args[0]->value->name);
        $constFetch = new ConstFetch(new Name('[]'));

        $processIdentical = $this->processIdentical($parent, $node, $compareVariable, $constFetch);
        if ($processIdentical !== null) {
            return $processIdentical;
        }

        return $this->processGreater($parent, $node, $compareVariable, $constFetch);
    }

    /**
     * @param Identical $binaryOp
     */
    private function processIdentical(
        BinaryOp $binaryOp,
        FuncCall $funcCall,
        Variable $compareVariable,
        ConstFetch $constFetch
    ): ?Variable {
        if ($binaryOp instanceof Identical && $binaryOp->right instanceof LNumber && $binaryOp->right->value === 0) {
            $this->removeNode($funcCall);
            $binaryOp->right = $constFetch;

            return $compareVariable;
        }

        if ($binaryOp instanceof Identical && $binaryOp->left instanceof LNumber && $binaryOp->left->value === 0) {
            $this->removeNode($funcCall);
            $binaryOp->left = $constFetch;

            return $compareVariable;
        }

        return null;
    }

    /**
     * @param Greater|Smaller $binaryOp
     */
    private function processGreater(
        BinaryOp $binaryOp,
        FuncCall $funcCall,
        Variable $compareVariable,
        ConstFetch $constFetch
    ): ?NotIdentical {
        if ($binaryOp instanceof Greater && $binaryOp->right instanceof LNumber && $binaryOp->right->value === 0) {
            $this->removeNode($funcCall);
            $this->removeNode($binaryOp->right);

            return new NotIdentical($compareVariable, $constFetch);
        }

        if ($binaryOp instanceof Smaller && $binaryOp->left instanceof LNumber && $binaryOp->left->value === 0) {
            $this->removeNode($funcCall);
            $this->removeNode($binaryOp->left);

            return new NotIdentical($constFetch, $compareVariable);
        }

        return null;
    }
}
