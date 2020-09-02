<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertResourceToClosedResourceRector\Fixture;

final class MyIsTypeTest2 extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $handler = fopen('php://temp', 'w');
        self::assertIsResource($handler);

        fclose($handler);
        self::assertIsNotResource($handler);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertResourceToClosedResourceRector\Fixture;

final class MyIsTypeTest2 extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $handler = fopen('php://temp', 'w');
        self::assertIsResource($handler);

        fclose($handler);
        self::assertIsClosedResource($handler);
    }
}

?>
