<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\ReplaceAssertArraySubsetRector\Fixture;

use PHPUnit\Framework\TestCase;

class AssertSameAssertEqual extends TestCase
{
    public function test()
    {
        $checkedArray = [];
        $expectedSubset = ['cache_directory' => 'two'];

        $this->assertArraySubset($expectedSubset, $checkedArray);

        $this->assertArraySubset($expectedSubset, $checkedArray, true);

        $this->assertArraySubset($expectedSubset, $checkedArray, false);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\ReplaceAssertArraySubsetRector\Fixture;

use PHPUnit\Framework\TestCase;

class AssertSameAssertEqual extends TestCase
{
    public function test()
    {
        $checkedArray = [];
        $expectedSubset = ['cache_directory' => 'two'];
        $this->assertArrayHasKey('cache_directory', $checkedArray);
        $this->assertEquals('two', $checkedArray['cache_directory']);
        $this->assertArrayHasKey('cache_directory', $checkedArray);
        $this->assertSame('two', $checkedArray['cache_directory']);
        $this->assertArrayHasKey('cache_directory', $checkedArray);
        $this->assertEquals('two', $checkedArray['cache_directory']);
    }
}

?>
