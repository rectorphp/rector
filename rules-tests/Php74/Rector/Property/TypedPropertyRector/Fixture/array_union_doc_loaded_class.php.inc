<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class ArrayUnionDocLoadedClass
{
    /** @var string[]|\stdClass[] */
    private $unionValue;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class ArrayUnionDocLoadedClass
{
    /** @var string[]|\stdClass[] */
    private array $unionValue;
}

?>
