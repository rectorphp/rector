<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertResourceToClosedResourceRector\Fixture;

final class MyIsTypeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $handler = fopen('php://temp', 'w');
        $this->assertIsResource($handler);

        fclose($handler);
        $this->assertIsNotResource($handler);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertResourceToClosedResourceRector\Fixture;

final class MyIsTypeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $handler = fopen('php://temp', 'w');
        $this->assertIsResource($handler);

        fclose($handler);
        $this->assertIsClosedResource($handler);
    }
}

?>
