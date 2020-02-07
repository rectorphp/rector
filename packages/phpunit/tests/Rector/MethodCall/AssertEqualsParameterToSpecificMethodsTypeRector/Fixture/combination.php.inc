<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector\Fixture;

use PHPUnit\Framework\TestCase;

final class RefactorCombination extends TestCase
{
    public function test()
    {
        $value = 'value';

        // $expected, $actual, string $message = '',
        // float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false)
        $this->assertEquals('string', $value, 'message', 100.0, 50, true, true);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector\Fixture;

use PHPUnit\Framework\TestCase;

final class RefactorCombination extends TestCase
{
    public function test()
    {
        $value = 'value';

        // $expected, $actual, string $message = '',
        // float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false)
        $this->assertEquals('string', $value, 'message');
        $this->assertEqualsIgnoringCase('string', $value, 'message');
        $this->assertEqualsCanonicalizing('string', $value, 'message');
        $this->assertEqualsWithDelta('string', $value, 100.0, 'message');
    }
}

?>
