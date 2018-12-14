<?php

namespace SomeNamespace;

use Rector\DomainDrivenDesign\Tests\Rector\ObjectToScalarRector\Source\SomeValueObject;

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

namespace SomeNamespace;

use Rector\DomainDrivenDesign\Tests\Rector\ObjectToScalarRector\Source\SomeValueObject;

class ThirdActionClass
{
    /**
     * @param null|SomeValueObject $name
     */
    public function someFunction(?string $name): ?string
    {
    }
}

?>
