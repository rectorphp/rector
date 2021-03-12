<?php

namespace Rector\Tests\PHPUnit\Rector\MethodCall\ExplicitPhpErrorApiRector\Fixture;

use PHPUnit\Framework\TestCase;

final class RefactorError extends TestCase
{
    public function test()
    {
        $this->expectException(\PHPUnit\Framework\TestCase\Error::class);
    }
}

?>
-----
<?php

namespace Rector\Tests\PHPUnit\Rector\MethodCall\ExplicitPhpErrorApiRector\Fixture;

use PHPUnit\Framework\TestCase;

final class RefactorError extends TestCase
{
    public function test()
    {
        $this->expectError();
    }
}

?>
