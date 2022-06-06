<?php

declare (strict_types=1);
namespace RectorPrefix20220606\PHPStan\Rules\PHPUnit;

use Countable;
use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\NodeAbstract;
use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Rules\Rule;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use function count;
use function strtolower;
/**
 * @implements Rule<NodeAbstract>
 */
class AssertSameWithCountRule implements Rule
{
    public function getNodeType() : string
    {
        return NodeAbstract::class;
    }
    public function processNode(Node $node, Scope $scope) : array
    {
        if (!AssertRuleHelper::isMethodOrStaticCallOnAssert($node, $scope)) {
            return [];
        }
        /** @var MethodCall|StaticCall $node */
        $node = $node;
        if (count($node->getArgs()) < 2) {
            return [];
        }
        if (!$node->name instanceof Node\Identifier || strtolower($node->name->name) !== 'assertsame') {
            return [];
        }
        $right = $node->getArgs()[1]->value;
        if ($right instanceof Node\Expr\FuncCall && $right->name instanceof Node\Name && strtolower($right->name->toString()) === 'count') {
            return ['You should use assertCount($expectedCount, $variable) instead of assertSame($expectedCount, count($variable)).'];
        }
        if ($right instanceof Node\Expr\MethodCall && $right->name instanceof Node\Identifier && strtolower($right->name->toString()) === 'count' && count($right->getArgs()) === 0) {
            $type = $scope->getType($right->var);
            if ((new ObjectType(Countable::class))->isSuperTypeOf($type)->yes()) {
                return ['You should use assertCount($expectedCount, $variable) instead of assertSame($expectedCount, $variable->count()).'];
            }
        }
        return [];
    }
}
