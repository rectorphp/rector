<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\FixtureNoDocBlock;

class ClassNameClass {
    private \Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\SourceNoDocBlock\AnotherClass $property;
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\FixtureNoDocBlock;

class ClassNameClass {
    private $property;
}

?>
