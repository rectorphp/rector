<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass;

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

use Rector\TypeDeclaration\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class ClassWithNullableProperty
{
    /**
     * @var AnotherClass|null
     */
    private ?\Rector\TypeDeclaration\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass $nullableClassWithDefaultNull = null;

    /**
     * @var null|AnotherClass
     */
    private ?\Rector\TypeDeclaration\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass $yetAnotherClass;
}

?>
