<?php

namespace Rector\Php56\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class SkipThisAssign
{
    private $val = 2;
}

$fn = function() {
    return $this->val;
};

$closure = \Closure::bind($fn, new SkipThisAssign(), '\Rector\Php56\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture\SkipThisAssign');
