<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertTrueFalseToSpecificMethodRector\Fixture;

use PHPUnit\Framework\TestCase;

final class IsReadable extends TestCase
{
    public function test()
    {
        $this->assertTrue(is_readable($readmeFile), sprintf('Cannot read "%s".', $readmeFile));
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertTrueFalseToSpecificMethodRector\Fixture;

use PHPUnit\Framework\TestCase;

final class IsReadable extends TestCase
{
    public function test()
    {
        $this->assertIsReadable($readmeFile, sprintf('Cannot read "%s".', $readmeFile));
    }
}

?>
