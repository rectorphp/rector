<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\NestedGetData;

final class AddFromChild
{
    public function getData(NestedGetData $nested)
    {
        return $nested->getData();
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\NestedGetData;

final class AddFromChild
{
    /**
     * @return string[]
     */
    public function getData(NestedGetData $nested)
    {
        return $nested->getData();
    }
}

?>
