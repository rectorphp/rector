<?php

namespace spec\Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use PhpSpec\ObjectBehavior;

class CurrencySpec extends ObjectBehavior
{
    public function it_returns_true()
    {
        $this->foo()->shouldEqual(true);
    }
}

?>
-----
<?php

namespace Tests\Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use PhpSpec\ObjectBehavior;

class CurrencyTest extends \PHPUnit\Framework\TestCase
{
    private \Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\Currency $currency;
    private \CurrencyData|\PHPUnit\Framework\MockObject\MockObject $data;
    protected function setUp()
    {
        parent::setUp();
        $this->currency = new \Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\Currency();
    }
    public function testReturnsTrue()
    {
        $this->assertEquals(true, $this->currency->foo());
    }
}

?>
