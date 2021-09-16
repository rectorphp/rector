<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class DoNotRemoveArrayUnionDocNotLoadedClass2
{
    /** @var string[][]|\NotLoadedClass[][] */
    private $unionValue2;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class DoNotRemoveArrayUnionDocNotLoadedClass2
{
    /** @var string[][]|\NotLoadedClass[][] */
    private array $unionValue2;
}

?>
