<?php

namespace spec\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

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

namespace Tests\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use PhpSpec\ObjectBehavior;

class DeliveryTest extends \PHPUnit\Framework\TestCase
{
    private \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\Delivery $delivery;
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
