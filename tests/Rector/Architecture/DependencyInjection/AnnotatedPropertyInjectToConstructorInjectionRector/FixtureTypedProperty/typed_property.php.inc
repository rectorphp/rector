<?php

namespace Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\FixtureTypedProperty;

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

namespace Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\FixtureTypedProperty;

use stdClass;

class TypedProperty
{
    private stdClass $property;
    public function __construct(\stdClass $property)
    {
        $this->property = $property;
    }
}

?>
