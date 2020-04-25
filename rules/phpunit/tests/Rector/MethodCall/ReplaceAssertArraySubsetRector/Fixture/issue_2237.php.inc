<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\ReplaceAssertArraySubsetRector\Fixture;

class Issue2237 extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $result = [
            'a' => 'item a',
            'b' => 'item k', // wrong value
            'c' => [
                // 'd' not present
                'g' => 'item g',
            ],
        ];

        $this->assertArraySubset([
            'a' => 'item a',
            'b' => 'item b',
            'c' => [
                'd' => 'item d',
            ],
        ], $result, true);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\ReplaceAssertArraySubsetRector\Fixture;

class Issue2237 extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $result = [
            'a' => 'item a',
            'b' => 'item k', // wrong value
            'c' => [
                // 'd' not present
                'g' => 'item g',
            ],
        ];
        $this->assertArrayHasKey('a', $result);
        $this->assertArrayHasKey('b', $result);
        $this->assertArrayHasKey('c', $result);
        $this->assertSame('item a', $result['a']);
        $this->assertSame('item b', $result['b']);
        $this->assertSame([
            'd' => 'item d',
        ], $result['c']);
    }
}

?>
