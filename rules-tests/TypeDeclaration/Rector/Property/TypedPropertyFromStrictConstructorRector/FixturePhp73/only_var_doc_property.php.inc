<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\FixturePhp73;

use stdClass;

final class OnlyVarDocProperty
{
    private $stdClass;

    public function __construct(stdClass $stdClass)
    {
        $this->stdClass = $stdClass;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\FixturePhp73;

use stdClass;

final class OnlyVarDocProperty
{
    /**
     * @var \stdClass
     */
    private $stdClass;

    public function __construct(stdClass $stdClass)
    {
        $this->stdClass = $stdClass;
    }
}

?>
