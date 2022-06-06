<?php

declare (strict_types=1);
namespace PHPStan\Rules\PHPUnit;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\NodeAbstract;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use function count;
use function strtolower;
/**
 * @implements Rule<NodeAbstract>
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
        /** @var MethodCall|StaticCall $node */
        $node = $node;
        if (\count($node->getArgs()) < 2) {
            return [];
        }
        if (!$node->name instanceof \PhpParser\Node\Identifier || \strtolower($node->name->name) !== 'assertsame') {
            return [];
        }
        $expectedArgumentValue = $node->getArgs()[0]->value;
        if (!$expectedArgumentValue instanceof \PhpParser\Node\Expr\ConstFetch) {
            return [];
        }
        if ($expectedArgumentValue->name->toLowerString() === 'null') {
            return ['You should use assertNull() instead of assertSame(null, $actual).'];
        }
        return [];
    }
}
