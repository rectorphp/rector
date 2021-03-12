<?php

namespace Rector\Tests\Restoration\Rector\ClassMethod\InferParamFromClassMethodReturnRector\Fixture;

use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Tests\Restoration\Rector\ClassMethod\InferParamFromClassMethodReturnRector\Source\SomeType;

class UnionTypes extends SomeType
{
    public function getNodeTypes(): array
    {
        return [String_::class, LNumber::class];
    }

    public function process(\PhpParser\Node $node)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Restoration\Rector\ClassMethod\InferParamFromClassMethodReturnRector\Fixture;

use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Tests\Restoration\Rector\ClassMethod\InferParamFromClassMethodReturnRector\Source\SomeType;

class UnionTypes extends SomeType
{
    public function getNodeTypes(): array
    {
        return [String_::class, LNumber::class];
    }

    /**
     * @param \PhpParser\Node\Scalar\LNumber|\PhpParser\Node\Scalar\String_ $node
     */
    public function process(\PhpParser\Node $node)
    {
    }
}

?>
