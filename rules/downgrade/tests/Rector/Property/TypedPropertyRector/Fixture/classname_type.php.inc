<?php

namespace Rector\Downgrade\Tests\Rector\Property\TypedPropertyRector\Fixture;

class ClassNameClass {
    private \Rector\Downgrade\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass $property;
}

?>
-----
<?php

namespace Rector\Downgrade\Tests\Rector\Property\TypedPropertyRector\Fixture;

class ClassNameClass {
    /**
     * @var \Rector\Downgrade\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass
     */
    private $property;
}

?>
