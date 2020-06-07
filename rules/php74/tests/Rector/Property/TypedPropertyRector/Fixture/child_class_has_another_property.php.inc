<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\SomeChildOfSomeParent;
use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\SomeParent;

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

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\SomeChildOfSomeParent;
use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\SomeParent;

class ChildClassHasAnotherProperty
{
    public \Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\SomeChildOfSomeParent $someChildOfSomeParent;
}

final class ChildClass extends ChildClassHasAnotherProperty
{
    public \Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\SomeParent $someParent;
}

?>
