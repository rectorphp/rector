<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

class PrivateVarNullUnused
{
    private $config = null;
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

class PrivateVarNullUnused
{
    /**
     * @var null
     */
    private $config = null;
}

?>