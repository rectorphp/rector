<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use PhpParser\Node\Expr\MethodCall;

final class SkipVariableDuplicated
{
    public function run($node)
    {
        $expectedMethodCall = new MethodCall($node->var, 'expects');
        $methodCall = new MethodCall($expectedMethodCall, $node->name);
    }
}
