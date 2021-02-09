<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass;

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

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class ClassWithNullableProperty
{
    private ?\Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass $nullableClassWithDefaultNull = null;

    private ?\Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass $yetAnotherClass = null;
}

?>
