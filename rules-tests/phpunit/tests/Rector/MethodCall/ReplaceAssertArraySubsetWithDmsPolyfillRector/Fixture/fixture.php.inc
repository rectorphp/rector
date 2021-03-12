<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\ReplaceAssertArraySubsetWithDmsPolyfillRector\Fixture;

use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function test()
    {
        self::assertArraySubset(['bar' => 0], ['bar' => '0'], true);

        $this->assertArraySubset(['bar' => 0], ['bar' => '0'], true);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\ReplaceAssertArraySubsetWithDmsPolyfillRector\Fixture;

use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function test()
    {
        \DMS\PHPUnitExtensions\ArraySubset\Assert::assertArraySubset(['bar' => 0], ['bar' => '0'], true);

        \DMS\PHPUnitExtensions\ArraySubset\Assert::assertArraySubset(['bar' => 0], ['bar' => '0'], true);
    }
}

?>
