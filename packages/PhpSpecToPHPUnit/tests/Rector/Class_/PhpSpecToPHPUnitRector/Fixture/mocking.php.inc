<?php

namespace spec\Rector\PhpSpecToPHPUnit\Tests\Rector\MethodCall\PhpSpecToPHPUnitRector\Fixture;

use Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecToPHPUnitRector\Source\DummyObjectBehavior;

class OrderSpec extends DummyObjectBehavior
{
    public function let(OrderFactory $factory, ShippingMethod $shippingMethod)
    {
        $factory->createShippingMethodFor(Argument::any())->shouldBeCalled()->willReturn($shippingMethod);
    }
}

?>
-----
<?php

namespace Tests\Rector\PhpSpecToPHPUnit\Tests\Rector\MethodCall\PhpSpecToPHPUnitRector\Fixture;

use Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecToPHPUnitRector\Source\DummyObjectBehavior;

class OrderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Rector\PhpSpecToPHPUnit\Tests\Rector\MethodCall\PhpSpecToPHPUnitRector\Fixture\Order
     */
    private $order;
    protected function setUp()
    {
        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(OrderFactory::class);
        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
        $shippingMethod = $this->createMock(ShippingMethod::class);
        $factory->expects($this->atLeastOnce())->method('createShippingMethodFor')->willReturn($shippingMethod);
    }
}

?>
