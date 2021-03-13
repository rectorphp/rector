<?php

namespace Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class SkipThisAssign
{
    private $val = 2;
}

$fn = function() {
    return $this->val;
};

$closure = \Closure::bind($fn, new SkipThisAssign(), '\Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture\SkipThisAssign');
