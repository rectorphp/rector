<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector\Fixture;

use ArrayAccess;

interface Bridge extends ArrayAccess
{
    public function offsetGet($offset): mixed;
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector\Fixture;

use ArrayAccess;

interface Bridge extends ArrayAccess
{
    /**
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset);
}

?>
