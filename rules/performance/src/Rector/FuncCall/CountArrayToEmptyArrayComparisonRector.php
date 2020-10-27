<?php

declare(strict_types=1);

namespace Rector\Performance\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\LNumber;
use PHPStan\Analyser\Scope;
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

        /** @var Variable $variable */
        $variable = $node->args[0]->value;

        /** @var Scope $scope */
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

        $compareVariable = new Variable($variable->name);
        $array = new Array_([]);

        $processIdentical = $this->processIdentical($parent, $node, $compareVariable, $array);
        if ($processIdentical !== null) {
            return $processIdentical;
        }

        return $this->processGreaterOrSmaller($parent, $node, $compareVariable, $array);
    }

    private function processIdentical(
        BinaryOp $binaryOp,
        FuncCall $funcCall,
        Variable $compareVariable,
        Array_ $array
    ): ?Variable {
        if ($binaryOp instanceof Identical && $binaryOp->right instanceof LNumber && $binaryOp->right->value === 0) {
            $this->removeNode($funcCall);
            $binaryOp->right = $array;

            return $compareVariable;
        }

        if ($binaryOp instanceof Identical && $binaryOp->left instanceof LNumber && $binaryOp->left->value === 0) {
            $this->removeNode($funcCall);
            $binaryOp->left = $array;

            return $compareVariable;
        }

        return null;
    }

    private function processGreaterOrSmaller(
        BinaryOp $binaryOp,
        FuncCall $funcCall,
        Variable $compareVariable,
        Array_ $array
    ): ?NotIdentical {
        if ($binaryOp instanceof Greater && $binaryOp->right instanceof LNumber && $binaryOp->right->value === 0) {
            $this->removeNode($funcCall);
            $this->removeNode($binaryOp->right);

            return new NotIdentical($compareVariable, $array);
        }

        if ($binaryOp instanceof Smaller && $binaryOp->left instanceof LNumber && $binaryOp->left->value === 0) {
            $this->removeNode($funcCall);
            $this->removeNode($binaryOp->left);

            return new NotIdentical($array, $compareVariable);
        }

        return null;
    }
}
