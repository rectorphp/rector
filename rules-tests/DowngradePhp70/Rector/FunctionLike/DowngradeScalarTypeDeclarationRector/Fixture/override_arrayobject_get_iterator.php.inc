<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector\Fixture;

class OverrideArrayObjectGetIterator extends \ArrayObject
{
    public function getIterator(): \Iterator
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector\Fixture;

class OverrideArrayObjectGetIterator extends \ArrayObject
{
    /**
     * @return \Iterator
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
    }
}

?>
