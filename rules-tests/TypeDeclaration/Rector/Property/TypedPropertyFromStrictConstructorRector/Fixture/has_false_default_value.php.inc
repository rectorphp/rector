<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

use stdClass;

class HasFalseDefaultValue
{
    /**
     * @var stdClass
     */
    private $stdClass = false;

    public function __construct(stdClass $stdClass)
    {
        $this->stdClass = $stdClass;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

use stdClass;

class HasFalseDefaultValue
{
    private \stdClass $stdClass;

    public function __construct(stdClass $stdClass)
    {
        $this->stdClass = $stdClass;
    }
}

?>
