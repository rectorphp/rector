<?php

namespace spec\Rector\PhpSpecToPHPUnit\Tests\Rector\MethodCall\PhpSpecToPHPUnitRector\Fixture;

use Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecToPHPUnitRector\Source\DummyObjectBehavior;

class DeliverySpec extends DummyObjectBehavior
{
    public function let(DeliveryFactory $factory, ShippingMethod $shippingMethod)
    {
        $factory->createShippingMethodFor(5)
            ->shouldBeCalled()
            ->willReturn($shippingMethod);
    }
}

?>
-----
<?php

namespace Tests\Rector\PhpSpecToPHPUnit\Tests\Rector\MethodCall\PhpSpecToPHPUnitRector\Fixture;

use Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecToPHPUnitRector\Source\DummyObjectBehavior;

class DeliveryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Rector\PhpSpecToPHPUnit\Tests\Rector\MethodCall\PhpSpecToPHPUnitRector\Fixture\Delivery
     */
    private $delivery;
    protected function setUp()
    {
        /** @var DeliveryFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(DeliveryFactory::class);
        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
        $shippingMethod = $this->createMock(ShippingMethod::class);
        $factory->expects($this->atLeastOnce())->method('createShippingMethodFor')->with($this->equalTo(5))
            ->willReturn($shippingMethod);
    }
}

?>
