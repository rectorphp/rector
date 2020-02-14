<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector\Fixture;

use PHPUnit\Framework\TestCase;

final class RefactorDelta extends TestCase
{
    public function test()
    {
        $value = 'value';

        $this->assertEquals('string', $value, 'message', 5.0);

        $this->assertEquals('string', $value, 'message', 0.0);

        $this->assertEquals('string', $value, 'message', 20.0);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector\Fixture;

use PHPUnit\Framework\TestCase;

final class RefactorDelta extends TestCase
{
    public function test()
    {
        $value = 'value';

        $this->assertEquals('string', $value, 'message');
        $this->assertEqualsWithDelta('string', $value, 5.0, 'message');

        $this->assertEquals('string', $value, 'message');

        $this->assertEquals('string', $value, 'message');
        $this->assertEqualsWithDelta('string', $value, 20.0, 'message');
    }
}

?>
