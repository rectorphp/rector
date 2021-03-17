<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

final class DefaultValueArrayMixed
{
    private $dreams = [];
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

final class DefaultValueArrayMixed
{
    /**
     * @var mixed[]
     */
    private $dreams = [];
}

?>
