<?php

namespace Rector\Tests\PHPUnit\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector\Fixture;

use PHPUnit\Framework\TestCase;

final class RemoveMaxDepth extends TestCase
{
    public function test()
    {
        $value = 'value';
        $this->assertEquals('string', $value, 'message', 0.0, 20);
    }
}

?>
-----
<?php

namespace Rector\Tests\PHPUnit\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector\Fixture;

use PHPUnit\Framework\TestCase;

final class RemoveMaxDepth extends TestCase
{
    public function test()
    {
        $value = 'value';
        $this->assertEquals('string', $value, 'message');
    }
}

?>
