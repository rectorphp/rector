<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\SomeChildOfSomeParent;
use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\SomeParent;

class ChildClassHasAnotherProperty
{
    /**
     * @var SomeChildOfSomeParent
     */
    public $someChildOfSomeParent;
}

final class ChildClass extends ChildClassHasAnotherProperty
{
    /**
     * @var SomeParent
     */
    public $someParent;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\SomeChildOfSomeParent;
use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\SomeParent;

class ChildClassHasAnotherProperty
{
    public \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\SomeChildOfSomeParent $someChildOfSomeParent;
}

final class ChildClass extends ChildClassHasAnotherProperty
{
    public \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\SomeParent $someParent;
}

?>
