<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AbstractParentClass;

final class FinalClassProtectedPropertyOnlyInCurrent extends AbstractParentClass
{
    /**
     * @var string
     */
    protected $somePropertyOnlyInCurrent;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AbstractParentClass;

final class FinalClassProtectedPropertyOnlyInCurrent extends AbstractParentClass
{
    protected string $somePropertyOnlyInCurrent;
}

?>
