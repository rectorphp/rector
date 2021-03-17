<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass;

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

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class ClassWithNullableProperty
{
    private ?\Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass $nullableClassWithDefaultNull = null;

    private ?\Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass $yetAnotherClass = null;
}

?>
