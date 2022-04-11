<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use PHPUnit\Framework\TestCase;
use stdClass;

final class ArrayTypeFilledDefaultNullInTearDownTest extends TestCase
{
    /**
     * @var stdClass
     */
    private $property;

    protected function setUp(): void
    {
        $this->property = new stdClass();
    }

    protected function tearDown(): void
    {
        $this->property = null;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use PHPUnit\Framework\TestCase;
use stdClass;

final class ArrayTypeFilledDefaultNullInTearDownTest extends TestCase
{
    private ?\stdClass $property = null;

    protected function setUp(): void
    {
        $this->property = new stdClass();
    }

    protected function tearDown(): void
    {
        $this->property = null;
    }
}

?>
