<?php

namespace Rector\Generic\Tests\Rector\Typehint\AddReturnTypeDeclarationRector\Fixture;

class RemoveReturnType
{
    public function clear(): array
    {
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Typehint\AddReturnTypeDeclarationRector\Fixture;

class RemoveReturnType
{
    public function clear()
    {
    }
}

?>
