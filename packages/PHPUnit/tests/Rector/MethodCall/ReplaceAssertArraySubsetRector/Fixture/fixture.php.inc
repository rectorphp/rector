<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\ReplaceAssertArraySubsetRector\Fixture;

class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $checkedArray = [];

        $this->assertArraySubset([
           'cache_directory' => 'new_value',
        ], $checkedArray);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\ReplaceAssertArraySubsetRector\Fixture;

class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $checkedArray = [];
        $this->assertArrayHasKey('cache_directory', $checkedArray);
        $this->assertSame('new_value', $checkedArray['cache_directory']);
    }
}

?>
