<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;

final class NotInTestsTypedPropertyInTests
{
    /**
     * @var int
     */
    private $value;

    public function setUp(): void
    {
        $this->value = 1000;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;

final class NotInTestsTypedPropertyInTests
{
    private ?int $value = null;

    public function setUp(): void
    {
        $this->value = 1000;
    }
}

?>
