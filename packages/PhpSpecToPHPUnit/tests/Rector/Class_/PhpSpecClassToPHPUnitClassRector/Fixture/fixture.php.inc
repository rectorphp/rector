<?php

namespace spec\Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecClassToPHPUnitClassRector\Fixture;

use Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecClassToPHPUnitClassRector\Source\DummyObjectBehavior;

class CreateMeSpec extends DummyObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(5);
    }

    public function it_returns_id()
    {
        $this->id()->shouldReturn(5);
    }

    public function it_blows()
    {
        $this->shouldThrow('SomeException')->during('item', [5]);
    }

    public function it_should_be_called()
    {
        $this->price()->shouldBeCalled()->willReturn(5);
        $this->shippingAddress(Argument::type(Address::class))->shouldBeCalled();
    }

    public function is_bool_check()
    {
        $this->hasFailed()->shouldBe(false);
        $this->hasFailed()->shouldNotBe(false);
    }
}

?>
-----
<?php

namespace Tests\Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecClassToPHPUnitClassRector\Fixture;

use Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecClassToPHPUnitClassRector\Source\DummyObjectBehavior;

class CreateMeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecClassToPHPUnitClassRector\Fixture\CreateMe
     */
    private $createMe;
    protected function setUp()
    {
        $this->createMe = new \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecClassToPHPUnitClassRector\Fixture\CreateMe(5);
    }

    public function testReturnsId()
    {
        $this->assertSame(5, $this->createMe->id());
    }

    public function testBlows()
    {
        $this->expectException('SomeException');
        $this->createMe->item([5]);
    }

    public function testCalled()
    {
        $this->assertSame(5, $this->createMe->price());
    }

    public function testBoolCheck()
    {
        $this->assertFalse($this->createMe->hasFailed());
        $this->assertNotFalse($this->createMe->hasFailed());
    }
}

?>
