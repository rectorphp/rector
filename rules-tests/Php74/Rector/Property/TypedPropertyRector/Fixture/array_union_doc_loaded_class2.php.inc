<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class ArrayUnionDocLoadedClass2
{
    /** @var string[][]|\stdClass[][] */
    private $unionValue;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class ArrayUnionDocLoadedClass2
{
    /** @var string[][]|\stdClass[][] */
    private array $unionValue;
}

?>
