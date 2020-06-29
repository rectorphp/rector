<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Param;

final class SkipNextSuffix
{
    public function run(Param $resolverParam)
    {
        $setDefaultsMethodCall = new MethodCall($resolverParam->var, new Identifier('setDefaults'));

        return $setDefaultsMethodCall;
    }
}
