<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class StaticProperty
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

use Rector\TypeDeclaration\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class StaticProperty
{
    private static iterable $iterable;
}

?>
