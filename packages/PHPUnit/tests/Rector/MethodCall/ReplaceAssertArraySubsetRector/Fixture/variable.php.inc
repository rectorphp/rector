<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\ReplaceAssertArraySubsetRector\Fixture;

class VariableTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $checkedArray = [];
        $expectedSubset = [
            'cache_directory' => 'new_value',
        ];

        $this->assertArraySubset($expectedSubset, $checkedArray);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\ReplaceAssertArraySubsetRector\Fixture;

class VariableTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $checkedArray = [];
        $expectedSubset = [
            'cache_directory' => 'new_value',
        ];
        $this->assertArrayHasKey('cache_directory', $checkedArray);
        $this->assertSame('new_value', $checkedArray['cache_directory']);
    }
}

?>
