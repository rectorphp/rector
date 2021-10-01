<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\SomeParent;

class ChildClass extends SomeParent
{
    /**
     * @var bool
     */
    protected $anAlreadyReplacedPropertyInParentClass = true;
}
?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\SomeParent;

class ChildClass extends SomeParent
{
    protected bool $anAlreadyReplacedPropertyInParentClass = true;
}
?>
