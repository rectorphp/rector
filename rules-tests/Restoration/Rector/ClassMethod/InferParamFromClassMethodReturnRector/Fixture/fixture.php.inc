<?php

namespace Rector\Tests\Restoration\Rector\ClassMethod\InferParamFromClassMethodReturnRector\Fixture;

use PhpParser\Node\Scalar\String_;
use Rector\Tests\Restoration\Rector\ClassMethod\InferParamFromClassMethodReturnRector\Source\SomeType;

class SomeClass extends SomeType
{
    public function getNodeTypes(): array
    {
        return [String_::class];
    }

    public function process(\PhpParser\Node $node)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Restoration\Rector\ClassMethod\InferParamFromClassMethodReturnRector\Fixture;

use PhpParser\Node\Scalar\String_;
use Rector\Tests\Restoration\Rector\ClassMethod\InferParamFromClassMethodReturnRector\Source\SomeType;

class SomeClass extends SomeType
{
    public function getNodeTypes(): array
    {
        return [String_::class];
    }

    /**
     * @param \PhpParser\Node\Scalar\String_ $node
     */
    public function process(\PhpParser\Node $node)
    {
    }
}

?>
