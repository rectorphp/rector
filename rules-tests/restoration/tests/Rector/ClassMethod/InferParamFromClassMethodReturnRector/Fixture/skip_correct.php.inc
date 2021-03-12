<?php

namespace Rector\Restoration\Tests\Rector\ClassMethod\InferParamFromClassMethodReturnRector\Fixture;

use PhpParser\Node\Scalar\String_;
use Rector\Restoration\Tests\Rector\ClassMethod\InferParamFromClassMethodReturnRector\Source\SomeType;

class SkipCorrect extends SomeType
{
    public function getNodeTypes(): array
    {
        return [String_::class];
    }

    /**
     * @param String_ $node
     */
    public function process(\PhpParser\Node $node)
    {
    }
}
