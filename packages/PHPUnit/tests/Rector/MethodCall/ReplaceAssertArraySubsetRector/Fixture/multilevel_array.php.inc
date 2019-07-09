<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\ReplaceAssertArraySubsetRector\Fixture;

class MultiLevelArray extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $checkedArray = [];
        $expectedSubset = [
            'cache_directory' => [
                [
                    'new_value' => true,
                ],
            ],
        ];

        $this->assertArraySubset($expectedSubset, $checkedArray);
    }

    public function otherTest()
    {
        $checkedArray = [];

        $this->assertArraySubset(
            [
                'cache_directory' => [
                    [
                        'new_value' => true,
                    ],
                ],
            ],
            $checkedArray
        );
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\ReplaceAssertArraySubsetRector\Fixture;

class MultiLevelArray extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $checkedArray = [];
        $expectedSubset = [
            'cache_directory' => [
                [
                    'new_value' => true,
                ],
            ],
        ];
        $this->assertArrayHasKey('cache_directory', $checkedArray);
        $this->assertSame([['new_value' => true]], $checkedArray['cache_directory']);
    }

    public function otherTest()
    {
        $checkedArray = [];
        $this->assertArrayHasKey('cache_directory', $checkedArray);
        $this->assertSame([
            [
                'new_value' => true,
            ],
        ], $checkedArray['cache_directory']);
    }
}

?>
