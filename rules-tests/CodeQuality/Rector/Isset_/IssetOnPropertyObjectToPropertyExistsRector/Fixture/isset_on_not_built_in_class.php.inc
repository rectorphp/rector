<?php

namespace Rector\Tests\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Source\SomeClass;

final class IssetOnNotBuiltInClass
{
    public function x(SomeClass $x) {
        isset($x->property);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Source\SomeClass;

final class IssetOnNotBuiltInClass
{
    public function x(SomeClass $x) {
        property_exists($x, 'property') && $x->property !== null;
    }
}

?>
