<?php

namespace spec\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use PhpSpec\ObjectBehavior;

class MockPropertiesNonLocalSpec extends ObjectBehavior
{
    public function let(OrderFactory $factory)
    {
        $this->beConstructedWith($factory);
    }

    public function let_it_go(AnotherMock $anotherMock)
    {
        $anotherMock->setName('Nummy');
        $this->addAnotherMock($anotherMock);
    }

    public function let_it_go_again(AnotherMock $anotherMock)
    {
        $anotherMock->setName('Nummy2');
        $this->addAnotherMock($anotherMock);
    }
}

?>
-----
<?php

namespace Tests\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use PhpSpec\ObjectBehavior;

class MockPropertiesNonLocalTest extends \PHPUnit\Framework\TestCase
{
    private \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\MockPropertiesNonLocal $mockPropertiesNonLocal;
    protected function setUp()
    {
        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(OrderFactory::class);
        $this->mockPropertiesNonLocal = new \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\MockPropertiesNonLocal($factory);
    }

    public function testLetItGo()
    {
        /** @var AnotherMock|\PHPUnit\Framework\MockObject\MockObject $anotherMock */
        $anotherMock = $this->createMock(AnotherMock::class);
        $anotherMock->setName('Nummy');
        $this->mockPropertiesNonLocal->addAnotherMock($anotherMock);
    }

    public function testLetItGoAgain()
    {
        /** @var AnotherMock|\PHPUnit\Framework\MockObject\MockObject $anotherMock */
        $anotherMock = $this->createMock(AnotherMock::class);
        $anotherMock->setName('Nummy2');
        $this->mockPropertiesNonLocal->addAnotherMock($anotherMock);
    }
}

?>
