<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use stdClass;

final class ObjectTypeFilledStdClass
{
    /**
     * @var object
     */
    private $property;

    public function __construct(stdClass $property)
    {
        $this->property = $property;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use stdClass;

final class ObjectTypeFilledStdClass
{
    private object $property;

    public function __construct(stdClass $property)
    {
        $this->property = $property;
    }
}

?>
