<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\FixtureNoDocBlock;

use Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\SourceNoDocBlock\AnotherClass;

class ClassNameNullableTypeClass
{
    private ?AnotherClass $property;
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\FixtureNoDocBlock;

use Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\SourceNoDocBlock\AnotherClass;

class ClassNameNullableTypeClass
{
    private $property;
}

?>
