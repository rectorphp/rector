<?php

namespace Rector\Tests\Php80\Rector\Class_\StringableForToStringRector\Fixture;

abstract class SomeAbstractMethod
{
    public abstract function __toString();
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\StringableForToStringRector\Fixture;

abstract class SomeAbstractMethod implements \Stringable
{
    public abstract function __toString(): string;
}

?>
