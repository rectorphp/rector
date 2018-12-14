<?php

namespace Rector\DomainDrivenDesign\Tests\Rector\ObjectToScalarDocBlockRector\Fixture;

use Rector\DomainDrivenDesign\Tests\Rector\ObjectToScalarDocBlockRector\Source\SomeValueObject;

class ThirdActionClass
{
    /**
     * @param null|SomeValueObject $name
     */
    public function someFunction(?SomeValueObject $name): ?SomeValueObject
    {
    }
}

?>
-----
<?php

namespace Rector\DomainDrivenDesign\Tests\Rector\ObjectToScalarDocBlockRector\Fixture;

use Rector\DomainDrivenDesign\Tests\Rector\ObjectToScalarDocBlockRector\Source\SomeValueObject;

class ThirdActionClass
{
    /**
     * @param null|string $name
     */
    public function someFunction(?SomeValueObject $name): ?SomeValueObject
    {
    }
}

?>
