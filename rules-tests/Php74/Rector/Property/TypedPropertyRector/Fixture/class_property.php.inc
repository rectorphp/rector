<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class ClassWithClassProperty
{
    /**
     * @var AnotherClass
     */
    private $anotherClass;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class ClassWithClassProperty
{
    private \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass $anotherClass;
}

?>
