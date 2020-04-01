<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\AddProphecyTraitRector\Fixture;

use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase
{
    public function testOne(): void
    {
        $prophecy = $this->prophesize(\AnInterface::class);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\AddProphecyTraitRector\Fixture;

use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase
{
    use \Prophecy\PhpUnit\ProphecyTrait;
    public function testOne(): void
    {
        $prophecy = $this->prophesize(\AnInterface::class);
    }
}

?>
