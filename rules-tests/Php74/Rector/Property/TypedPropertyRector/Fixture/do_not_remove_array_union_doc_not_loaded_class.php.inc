<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class DoNotRemoveArrayUnionDocNotLoadedClass
{
    /** @var string[]|\NotLoadedClass[] */
    private $unionValue;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class DoNotRemoveArrayUnionDocNotLoadedClass
{
    /** @var string[]|\NotLoadedClass[] */
    private array $unionValue;
}

?>
