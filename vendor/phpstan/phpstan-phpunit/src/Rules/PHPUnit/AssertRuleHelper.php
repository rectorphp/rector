<?php

declare (strict_types=1);
namespace PHPStan\Rules\PHPUnit;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use function in_array;
use function strtolower;
class AssertRuleHelper
{
    public static function isMethodOrStaticCallOnAssert(Node $node, Scope $scope) : bool
    {
        $testCaseType = new ObjectType('RectorPrefix202208\\PHPUnit\\Framework\\Assert');
        if ($node instanceof Node\Expr\MethodCall) {
            $calledOnType = $scope->getType($node->var);
        } elseif ($node instanceof Node\Expr\StaticCall) {
            if ($node->class instanceof Node\Name) {
                $class = (string) $node->class;
                if ($scope->isInClass() && in_array(strtolower($class), ['self', 'static', 'parent'], \true)) {
                    $calledOnType = new ObjectType($scope->getClassReflection()->getName());
                } else {
                    $calledOnType = new ObjectType($class);
                }
            } else {
                $calledOnType = $scope->getType($node->class);
            }
        } else {
            return \false;
        }
        if (!$testCaseType->isSuperTypeOf($calledOnType)->yes()) {
            return \false;
        }
        return \true;
    }
}
