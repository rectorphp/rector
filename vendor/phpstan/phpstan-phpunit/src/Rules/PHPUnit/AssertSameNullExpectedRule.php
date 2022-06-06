<?php

declare (strict_types=1);
namespace RectorPrefix20220606\PHPStan\Rules\PHPUnit;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\ConstFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\NodeAbstract;
use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Rules\Rule;
use function count;
use function strtolower;
/**
 * @implements Rule<NodeAbstract>
 */
class AssertSameNullExpectedRule implements Rule
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
        $expectedArgumentValue = $node->getArgs()[0]->value;
        if (!$expectedArgumentValue instanceof ConstFetch) {
            return [];
        }
        if ($expectedArgumentValue->name->toLowerString() === 'null') {
            return ['You should use assertNull() instead of assertSame(null, $actual).'];
        }
        return [];
    }
}
