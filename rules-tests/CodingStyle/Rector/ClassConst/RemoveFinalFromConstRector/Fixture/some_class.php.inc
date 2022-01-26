<?php

namespace Rector\Tests\CodingStyle\Rector\ClassConst\RemoveFinalFromConstRector\Fixture;

final class SomeClass
{
    final public const NAME = 'value';
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\ClassConst\RemoveFinalFromConstRector\Fixture;

final class SomeClass
{
    public const NAME = 'value';
}

?>
