<?php

declare(strict_types=1);

namespace Rector\Performance\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
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
 * @see \Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRule\CountArrayToEmptyArrayComparisonRuleTest
 */
final class CountArrayToEmptyArrayComparisonRule extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change count array comparison to empty array comparison to improve performance', [
            new CodeSample(
                <<<'CODE_SAMPLE'
count($array) === 0;
count($array) >= 0;
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

        // more than 1 arg, skip
        // not check possible mode: COUNT_RECURSIVE in 2nd parameter
        if (isset($args[1])) {
            return null;
        }

        $variable = $args[0]->value;
        $scope = $variable->getAttribute(AttributeKey::SCOPE);
        $type = $scope->getType($variable);

        // no pass array type, skip
        if (! $type instanceof ArrayType) {
            return null;
        }

        $compareVariable = new Variable($args[0]->value->name);
        $compareValue = new ConstFetch(new Name('[]'));
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);

        if ($parent instanceof Identical && $parent->right instanceof LNumber && $parent->right->value === 0) {
            $this->removeNode($node);

            $parent->right = $compareValue;
            $node = $compareVariable;

            return $node;
        }

        // @todo
        // 2. check parent is a >= 0, no? skip
        // 3. replace with !== []

        return $node;
    }
}
