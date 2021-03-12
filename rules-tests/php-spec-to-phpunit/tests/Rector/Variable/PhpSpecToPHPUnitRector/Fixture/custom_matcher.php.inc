<?php

namespace spec\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use PhpSpec\ObjectBehavior;

class RatesSpec extends ObjectBehavior
{
    public function let(Provider $provider)
    {
        $provider->get()->shouldBeCalled()->willReturn(
            '08.12.2017 #237
            země|měna|množství|kód|kurz
            Austrálie|dolar|1|AUD|16,362
            Velká Británie|libra|1|GBP|29,194'
        );
        $this->beConstructedWith($provider);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Rates::class);
    }

    public function it_should_load_current_rates(Provider $provider)
    {
        $provider->get()->shouldBeCalled()->willReturn(
            '08.12.2017 #237
            země|měna|množství|kód|kurz
            Austrálie|dolar|1|AUD|16,362
            Velká Británie|libra|1|GBP|29,194'
        );

        $this->load()->shouldReturnRates(new ArrayCollection([
            new Rate('AUD', '16.362'),
            new Rate('GBP', '29.194'),
        ]));
    }

    public function getMatchers(): array
    {
        return [
            'returnRates' => function (ArrayCollection $rates, ArrayCollection $expectedRates) {
                foreach ($rates as $index => $rate) {
                    if ($rate->rate !== $expectedRates[$index]->rate || $rate->currency !== $expectedRates[$index]->currency) {
                        return false;
                    }
                }

                return true;
            },
        ];
    }
}

?>
-----
<?php

namespace Tests\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use PhpSpec\ObjectBehavior;

class RatesTest extends \PHPUnit\Framework\TestCase
{
    private \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\Rates $rates;
    private \PHPUnit\Framework\MockObject\MockObject|\Provider $provider;
    protected function setUp()
    {
        $this->provider = $this->createMock(Provider::class);
        $this->provider->expects($this->atLeastOnce())->method('get')->willReturn(
            '08.12.2017 #237
            země|měna|množství|kód|kurz
            Austrálie|dolar|1|AUD|16,362
            Velká Británie|libra|1|GBP|29,194'
        );
        $this->rates = new \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\Rates($this->provider);
    }

    public function testInitializable()
    {
        $this->assertInstanceOf(Rates::class, $this->rates);
    }

    public function testLoadCurrentRates()
    {
        $this->provider->expects($this->atLeastOnce())->method('get')->willReturn(
            '08.12.2017 #237
            země|měna|množství|kód|kurz
            Austrálie|dolar|1|AUD|16,362
            Velká Británie|libra|1|GBP|29,194'
        );
        $matcherCallable = $this->getMatchers()['returnRates'];
        $matcherCallable(new ArrayCollection([
            new Rate('AUD', '16.362'),
            new Rate('GBP', '29.194'),
        ]), $this->rates->load());
    }

    public function getMatchers(): array
    {
        return [
            'returnRates' => function (ArrayCollection $rates, ArrayCollection $expectedRates) {
                foreach ($rates as $index => $rate) {
                    if ($rate->rate !== $expectedRates[$index]->rate || $rate->currency !== $expectedRates[$index]->currency) {
                        return false;
                    }
                }

                return true;
            },
        ];
    }
}

?>
