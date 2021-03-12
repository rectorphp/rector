<?php

namespace Rector\Tests\MockeryToProphecy\Rector\StaticCall\MockeryToProphecyRector\Fixture;

use PHPUnit\Framework\TestCase;

final class MockeryClose extends TestCase
{
    public function tearDown(): void
    {
        \Mockery::close();
    }
}

?>
-----
<?php

namespace Rector\Tests\MockeryToProphecy\Rector\StaticCall\MockeryToProphecyRector\Fixture;

use PHPUnit\Framework\TestCase;

final class MockeryClose extends TestCase
{
    public function tearDown(): void
    {
    }
}

?>
