<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class ClassWithStaticProperty
{
    /**
     * @var iterable
     */
    private static $iterable;
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class ClassWithStaticProperty
{
    private static iterable $iterable;
}

?>
