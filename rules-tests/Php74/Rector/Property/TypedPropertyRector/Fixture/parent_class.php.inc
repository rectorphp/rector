<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

class ParentClass
{
    /**
     * @var bool
     */
    protected $active = false;
}

class ChildClass extends ParentClass
{
    /**
     * @var bool
     */
    protected $active = true;
}
?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

class ParentClass
{
    protected bool $active = false;
}

class ChildClass extends ParentClass
{
    protected bool $active = true;
}
?>
