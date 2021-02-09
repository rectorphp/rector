<?php

namespace Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\FixturePhp74;

use stdClass;

class TypedProperty
{
    /**
     * @inject
     */
    public stdClass $property;
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\FixturePhp74;

use stdClass;

class TypedProperty
{
    public function __construct(private \stdClass $property)
    {
    }
}

?>
