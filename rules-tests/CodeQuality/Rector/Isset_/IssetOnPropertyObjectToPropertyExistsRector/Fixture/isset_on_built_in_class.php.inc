<?php

namespace Rector\Tests\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

use stdClass;

final class IssetOnBuiltInClass
{
    public function x(stdClass $x) {
        isset($x->property);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

use stdClass;

final class IssetOnBuiltInClass
{
    public function x(stdClass $x) {
        property_exists($x, 'property') && $x->property !== null;
    }
}

?>
