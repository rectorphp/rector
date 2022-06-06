<?php

declare (strict_types=1);
namespace RectorPrefix20220606\PHPStan\Rules\PHPUnit;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use function in_array;
use function strtolower;
class AssertRuleHelper
{
    public static function isMethodOrStaticCallOnAssert(Node $node, Scope $scope) : bool
    {
        $testCaseType = new ObjectType('RectorPrefix20220606\\PHPUnit\\Framework\\Assert');
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
