<?php

namespace spec\Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use PhpSpec\ObjectBehavior;

class DeliverySpec extends ObjectBehavior
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

namespace Tests\Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use PhpSpec\ObjectBehavior;

class DeliveryTest extends \PHPUnit\Framework\TestCase
{
    private \Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\Delivery $delivery;
    protected function setUp()
    {
        /** @var DeliveryFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(DeliveryFactory::class);
        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
        $shippingMethod = $this->createMock(ShippingMethod::class);
        $factory->expects($this->atLeastOnce())
            ->method('createShippingMethodFor')->with($this->equalTo(5))
            ->willReturn($shippingMethod);
    }
}

?>
