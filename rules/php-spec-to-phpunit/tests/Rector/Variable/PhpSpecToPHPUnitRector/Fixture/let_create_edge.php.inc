<?php

namespace spec\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use PhpSpec\ObjectBehavior;

class CurrencySpec extends ObjectBehavior
{
    public function let(CurrencyData $data)
    {
        $data->code = 'CZK';

        $this->beConstructedThrough('create', [$data]);
    }

    public function it_should_not_be_constructed_without_code(CurrencyData $data)
    {
        $data->code = '';

        $this->beConstructedThrough('create', [$data]);
        $this->shouldThrow(ValidationException::class)->duringInstantiation();
    }
}

?>
-----
<?php

namespace Tests\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use PhpSpec\ObjectBehavior;

class CurrencyTest extends \PHPUnit\Framework\TestCase
{
    private \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\Currency $currency;
    private \CurrencyData|\PHPUnit\Framework\MockObject\MockObject $data;
    protected function setUp()
    {
        $this->data = $this->createMock(CurrencyData::class);
        $this->data->code = 'CZK';
        $this->currency = \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\Currency::create($this->data);
    }

    public function testNotBeConstructedWithoutCode()
    {
        $this->data->code = '';
        $this->expectException(ValidationException::class);
        $this->currency = \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\Currency::create($this->data);
    }
}

?>
