<?php

namespace Rector\DomainDrivenDesign\Tests\Rector\ValueObjectRemoverDocBlockRector\Fixture;

use Rector\DomainDrivenDesign\Tests\Rector\ValueObjectRemoverDocBlockRector\Source\SomeValueObject;

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

namespace Rector\DomainDrivenDesign\Tests\Rector\ValueObjectRemoverDocBlockRector\Fixture;

use Rector\DomainDrivenDesign\Tests\Rector\ValueObjectRemoverDocBlockRector\Source\SomeValueObject;

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
