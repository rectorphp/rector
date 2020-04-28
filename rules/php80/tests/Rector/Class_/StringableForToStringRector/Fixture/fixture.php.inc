<?php

namespace Rector\Php80\Tests\Rector\Class_\StringableForToStringRector\Fixture;

class SomeClass
{
    public function __toString()
    {
        return 'I can stringz';
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Class_\StringableForToStringRector\Fixture;

class SomeClass implements \Stringable
{
    public function __toString(): string
    {
        return 'I can stringz';
    }
}

?>
