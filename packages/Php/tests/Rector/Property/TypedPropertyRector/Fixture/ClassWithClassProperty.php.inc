<?php

namespace Rector\Php\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Php\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass;

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

namespace Rector\Php\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Php\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class ClassWithClassProperty
{
    private AnotherClass $anotherClass;
}

?>
