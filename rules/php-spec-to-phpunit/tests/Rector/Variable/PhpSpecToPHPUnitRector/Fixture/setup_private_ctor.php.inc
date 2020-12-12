<?php

namespace spec\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use PhpSpec\ObjectBehavior;

class ResultSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->beConstructedThrough('success');
        $this->shouldHaveType(Result::class);
    }

    public function it_should_succeed()
    {
        $this->beConstructedThrough('fail');
        $this->hasFailed()->shouldReturn(false);
    }
}

?>
-----
<?php

namespace Tests\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use PhpSpec\ObjectBehavior;

class ResultTest extends \PHPUnit\Framework\TestCase
{
    private \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\Result $result;
    public function testInitializable()
    {
        $this->result = \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\Result::success();
        $this->assertInstanceOf(Result::class, $this->result);
    }

    public function testSucceed()
    {
        $this->result = \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\Result::fail();
        $this->assertFalse($this->result->hasFailed());
    }
}

?>
