<?php

namespace SomeNamespace;

class SecondActionClass
{
    public function someFunction(\Rector\DomainDrivenDesign\Tests\Rector\ValueObjectRemoverRector\Source\SomeChildOfValueObject $name)
    {
    }
}

?>
-----
<?php

namespace SomeNamespace;

class SecondActionClass
{
    public function someFunction(string $name)
    {
    }
}

?>
