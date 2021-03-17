<?php

namespace Rector\Tests\Restoration\Rector\ClassMethod\InferParamFromClassMethodReturnRector\Fixture;

use PhpParser\Node\Scalar\String_;
use Rector\Tests\Restoration\Rector\ClassMethod\InferParamFromClassMethodReturnRector\Source\SomeType;

class SkipEmptyArray extends SomeType
{
    public function getNodeTypes(): array
    {
        return [];
    }

    public function process(\PhpParser\Node $node)
    {
    }
}
