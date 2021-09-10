<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

use stdClass;

class NullableHasDefaultValue
{
    /**
     * @var stdClass|null
     */
    private $stdClass = null;

    public function __construct(?stdClass $stdClass)
    {
        $this->stdClass = $stdClass;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

use stdClass;

class NullableHasDefaultValue
{
    private ?\stdClass $stdClass;

    public function __construct(?stdClass $stdClass)
    {
        $this->stdClass = $stdClass;
    }
}

?>
