<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector\Fixture;

class PrivateVarNullUnused
{
    private $config = null;
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector\Fixture;

class PrivateVarNullUnused
{
    /**
     * @var null
     */
    private $config = null;
}

?>