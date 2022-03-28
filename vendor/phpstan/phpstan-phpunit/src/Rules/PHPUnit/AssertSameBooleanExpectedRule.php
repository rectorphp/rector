<?php

declare (strict_types=1);
namespace PHPStan\Rules\PHPUnit;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\NodeAbstract;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Type\Constant\ConstantBooleanType;
use function count;
use function strtolower;
/**
 * @implements Rule<NodeAbstract>
 */
class AssertSameBooleanExpectedRule implements \PHPStan\Rules\Rule
{
    public function getNodeType() : string
    {
        return \PhpParser\NodeAbstract::class;
    }
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        if (!\PHPStan\Rules\PHPUnit\AssertRuleHelper::isMethodOrStaticCallOnAssert($node, $scope)) {
            return [];
        }
        /** @var MethodCall|StaticCall $node */
        $node = $node;
        if (\count($node->getArgs()) < 2) {
            return [];
        }
        if (!$node->name instanceof \PhpParser\Node\Identifier || \strtolower($node->name->name) !== 'assertsame') {
            return [];
        }
        $leftType = $scope->getType($node->getArgs()[0]->value);
        if (!$leftType instanceof \PHPStan\Type\Constant\ConstantBooleanType) {
            return [];
        }
        if ($leftType->getValue()) {
            return ['You should use assertTrue() instead of assertSame() when expecting "true"'];
        }
        return ['You should use assertFalse() instead of assertSame() when expecting "false"'];
    }
}
