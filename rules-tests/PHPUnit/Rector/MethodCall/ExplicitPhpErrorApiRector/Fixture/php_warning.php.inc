<?php

namespace Rector\Tests\PHPUnit\Rector\MethodCall\ExplicitPhpErrorApiRector\Fixture;

use PHPUnit\Framework\TestCase;

final class RefactorWarning extends TestCase
{
    public function test()
    {
        $this->expectException(\PHPUnit\Framework\TestCase\Warning::class);
    }
}

?>
-----
<?php

namespace Rector\Tests\PHPUnit\Rector\MethodCall\ExplicitPhpErrorApiRector\Fixture;

use PHPUnit\Framework\TestCase;

final class RefactorWarning extends TestCase
{
    public function test()
    {
        $this->expectWarning();
    }
}

?>
