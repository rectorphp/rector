<?php

namespace Rector\Php\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Php\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class ClassWithNullableProperty
{
    /**
     * @var AnotherClass|null
     */
    private $nullableClassWithDefaultNull = null;

    /**
     * @var null|AnotherClass
     */
    private $yetAnotherClass;
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Php\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class ClassWithNullableProperty
{
    /**
     * @var AnotherClass|null
     */
    private ?\Rector\Php\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass $nullableClassWithDefaultNull = null;

    /**
     * @var null|AnotherClass
     */
    private ?\Rector\Php\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass $yetAnotherClass;
}

?>
