<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\WithConsecutiveArgToArrayRector\Fixture;

class SomeClass
{
    public function run($one, $two)
    {
    }
}

class SomeTestCase extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $someClassMock = $this->createMock(SomeClass::class);
        $someClassMock->expects($this->exactly(2))
            ->method('run')
            ->withConsecutive(1, 2, 3, 5);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\WithConsecutiveArgToArrayRector\Fixture;

class SomeClass
{
    public function run($one, $two)
    {
    }
}

class SomeTestCase extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $someClassMock = $this->createMock(SomeClass::class);
        $someClassMock->expects($this->exactly(2))
            ->method('run')->withConsecutive([1, 2], [3, 5]);
    }
}

?>
