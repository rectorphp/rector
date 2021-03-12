<?php

namespace spec\Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\ItIsMe;
use PhpSpec\ObjectBehavior;

class ItIsMeSpec extends ObjectBehavior
{
    public function it_is_me()
    {
        $this->shouldHaveType(ItIsMe::class);
    }
}

?>
-----
<?php

namespace Tests\Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\ItIsMe;
use PhpSpec\ObjectBehavior;

class ItIsMeTest extends \PHPUnit\Framework\TestCase
{
    private \Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\ItIsMe $itIsMe;
    protected function setUp()
    {
        parent::setUp();
        $this->itIsMe = new \Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\ItIsMe();
    }
}

?>
