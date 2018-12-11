<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector\Fixture;

use PHPUnit\Framework\TestCase;

final class RefactorCanonize extends TestCase
{
    public function test()
    {
        $value = 'value';

        $this->assertEquals('string', $value, 'message', 0.0, 10, true);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector\Fixture;

use PHPUnit\Framework\TestCase;

final class RefactorCanonize extends TestCase
{
    public function test()
    {
        $value = 'value';

        $this->assertEquals('string', $value, 'message');
        $this->assertEqualsCanonicalizing('string', $value, 'message');
    }
}

?>
