<?php

declare (strict_types=1);
namespace PHPStan\Rules\PHPUnit;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Type\NullType;
/**
 * @implements \PHPStan\Rules\Rule<\PhpParser\NodeAbstract>
 */
class AssertSameNullExpectedRule implements \PHPStan\Rules\Rule
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
        /** @var \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node */
        $node = $node;
        if (\count($node->getArgs()) < 2) {
            return [];
        }
        if (!$node->name instanceof \PhpParser\Node\Identifier || \strtolower($node->name->name) !== 'assertsame') {
            return [];
        }
        $leftType = $scope->getType($node->getArgs()[0]->value);
        if ($leftType instanceof \PHPStan\Type\NullType) {
            return ['You should use assertNull() instead of assertSame(null, $actual).'];
        }
        return [];
    }
}
