<?php

namespace spec\Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecToPHPUnitRector\Fixture;

use Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecToPHPUnitRector\Source\DummyObjectBehavior;

class CurrencySpec extends DummyObjectBehavior
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

namespace Tests\Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecToPHPUnitRector\Fixture;

use Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecToPHPUnitRector\Source\DummyObjectBehavior;

class CurrencyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecToPHPUnitRector\Fixture\Currency
     */
    private $currency;
    protected function setUp()
    {
        /** @var CurrencyData|\PHPUnit\Framework\MockObject\MockObject $data */
        $data = $this->createMock(CurrencyData::class);
        $data->code = 'CZK';
        $this->currency = \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecToPHPUnitRector\Fixture\Currency::create($data);
    }

    public function testNotBeConstructedWithoutCode()
    {
        /** @var CurrencyData|\PHPUnit\Framework\MockObject\MockObject $data */
        $data = $this->createMock(CurrencyData::class);
        $data->code = '';
        $this->expectException(ValidationException::class);
        $this->currency = \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecToPHPUnitRector\Fixture\Currency::create($data);
    }
}

?>
