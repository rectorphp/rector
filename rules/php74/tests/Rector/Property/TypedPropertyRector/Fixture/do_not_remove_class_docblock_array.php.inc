<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class DonotRemoveClassDocblockArray
{
    /**
     * @var \DateTime[]|null
     */
    private $times2;
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class DonotRemoveClassDocblockArray
{
    /**
     * @var \DateTime[]|null
     */
    private ?array $times2 = null;
}

?>
