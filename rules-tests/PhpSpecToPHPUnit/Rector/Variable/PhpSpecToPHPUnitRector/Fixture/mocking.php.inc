<?php

namespace spec\Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use PhpSpec\ObjectBehavior;

class OrderSpec extends ObjectBehavior
{
    public function let(OrderFactory $factory, ShippingMethod $shippingMethod)
    {
        $factory->createShippingMethodFor(Argument::any())->shouldBeCalled()->willReturn($shippingMethod);
        $factory->anotherMethod(25)->shouldBeCalled()->willReturn($shippingMethod);
    }
}

?>
-----
<?php

namespace Tests\Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use PhpSpec\ObjectBehavior;

class OrderTest extends \PHPUnit\Framework\TestCase
{
    private \Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\Order $order;
    protected function setUp()
    {
        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(OrderFactory::class);
        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
        $shippingMethod = $this->createMock(ShippingMethod::class);
        $factory->expects($this->atLeastOnce())->method('createShippingMethodFor')->willReturn($shippingMethod);
        $factory->expects($this->atLeastOnce())->method('anotherMethod')->with($this->equalTo(25))->willReturn($shippingMethod);
    }
}

?>
