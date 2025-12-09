<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Expression;
use Rector\PHPUnit\CodeQuality\ValueObject\VariableNameToType;
final class AssertMethodCallFactory
{
    public function createAssertInstanceOf(VariableNameToType $variableNameToType): Expression
    {
        $args = [new Arg(new ClassConstFetch(new FullyQualified($variableNameToType->getObjectType()), 'class')), new Arg(new Variable($variableNameToType->getVariableName()))];
        $methodCall = new MethodCall(new Variable('this'), 'assertInstanceOf', $args);
        return new Expression($methodCall);
    }
}
