<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

final class DefaultValueArrayMixed
{
    private $dreams = [];
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

final class DefaultValueArrayMixed
{
    /**
     * @var mixed[]
     */
    private $dreams = [];
}

?>
