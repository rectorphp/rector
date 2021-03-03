<?php

namespace Rector\Transform\Tests\Rector\Class_\NativeTestCaseRector\Fixture;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SomeClassTest extends AbstractRectorTestCase
{
    public function getRectorClass(): string
    {
        return SomeRector::class;
    }
}

?>
-----
<?php

namespace Rector\Transform\Tests\Rector\Class_\NativeTestCaseRector\Fixture;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SomeClassTest extends AbstractRectorTestCase
{
    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}

?>
