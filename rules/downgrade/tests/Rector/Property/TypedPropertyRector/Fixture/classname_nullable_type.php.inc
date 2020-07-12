<?php

namespace Rector\Downgrade\Tests\Rector\Property\TypedPropertyRector\Fixture;

class ClassNameNullableTypeClass {
    private ?\Rector\Downgrade\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass $property;
}

?>
-----
<?php

namespace Rector\Downgrade\Tests\Rector\Property\TypedPropertyRector\Fixture;

class ClassNameNullableTypeClass {
    /**
     * @var \Rector\Downgrade\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass|null
     */
    private $property;
}

?>
