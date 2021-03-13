<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class DoNotRemoveClassDocblockArray
{
    /**
     * @var \DateTime[]|null
     */
    private $times2;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class DoNotRemoveClassDocblockArray
{
    /**
     * @var \DateTime[]|null
     */
    private ?array $times2 = null;
}

?>
