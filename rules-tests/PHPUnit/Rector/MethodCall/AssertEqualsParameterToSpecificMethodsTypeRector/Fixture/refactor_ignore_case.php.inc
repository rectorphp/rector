<?php

namespace Rector\Tests\PHPUnit\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector\Fixture;

use PHPUnit\Framework\TestCase;

final class RefactorIgnoreCase extends TestCase
{
    public function test()
    {
        $value = 'value';

        $this->assertEquals('string', $value, 'message', 0.0, 10, false, true);
    }
}

?>
-----
<?php

namespace Rector\Tests\PHPUnit\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector\Fixture;

use PHPUnit\Framework\TestCase;

final class RefactorIgnoreCase extends TestCase
{
    public function test()
    {
        $value = 'value';

        $this->assertEquals('string', $value, 'message');
        $this->assertEqualsIgnoringCase('string', $value, 'message');
    }
}

?>
