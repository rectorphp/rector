<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\Testing\PHPUnit\AbstractTestCase;

final class TypedPropertyInTests extends AbstractTestCase
{
    /**
     * @var PhpDocInfoPrinter
     */
    private $phpDocInfoPrinter;

    protected function setUp(): void
    {
        $this->phpDocInfoPrinter = $this->getService(PhpDocInfoPrinter::class);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\Testing\PHPUnit\AbstractTestCase;

final class TypedPropertyInTests extends AbstractTestCase
{
    private \Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter $phpDocInfoPrinter;

    protected function setUp(): void
    {
        $this->phpDocInfoPrinter = $this->getService(PhpDocInfoPrinter::class);
    }
}

?>
