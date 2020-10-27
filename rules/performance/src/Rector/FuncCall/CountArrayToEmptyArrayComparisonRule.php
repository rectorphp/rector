<?php

declare(strict_types=1);

namespace Rector\Performance\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name\FullyQualified;
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

        // @todo
        // 2. check parent is a comparison with === 0 or >= 0, no? skip
        // 3. replace with comparison to [] or !== []

        return $node;
    }
}
