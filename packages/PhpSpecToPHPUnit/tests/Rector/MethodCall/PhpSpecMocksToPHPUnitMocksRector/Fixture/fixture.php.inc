<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\MethodCall\PhpSpecMocksToPHPUnitMocksRector\Fixture;

use Rector\PhpSpecToPHPUnit\Tests\Rector\MethodCall\PhpSpecMocksToPHPUnitMocksRector\Source\ForMethodsDummyObjectBehavior;

class OrderSpec extends ForMethodsDummyObjectBehavior
{
    public function let(OrderFactory $factory, ShippingMethod $shippingMethod)
    {
        $factory->createShippingMethodFor(Argument::any())->shouldBeCalled()->willReturn($shippingMethod);
    }
}

?>
-----
<?php

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\MethodCall\PhpSpecMocksToPHPUnitMocksRector\Fixture;

use Rector\PhpSpecToPHPUnit\Tests\Rector\MethodCall\PhpSpecMocksToPHPUnitMocksRector\Source\ForMethodsDummyObjectBehavior;

class OrderSpec extends ForMethodsDummyObjectBehavior
{
    public function let()
    {
        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(OrderFactory::class);
        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
        $shippingMethod = $this->createMock(ShippingMethod::class);
        $factory->method('createShippingMethodFor')->expects($this->once())->willReturn($shippingMethod);
    }
}

?>
