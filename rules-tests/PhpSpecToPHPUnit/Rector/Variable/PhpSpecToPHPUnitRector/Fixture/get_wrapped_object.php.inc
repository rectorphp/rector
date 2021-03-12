<?php

namespace spec\Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use PhpSpec\ObjectBehavior;

class BlablaSpec extends ObjectBehavior
{
    public function it_is_me(SomeType $someType)
    {
        $assignMe = $someType->getWrappedObject();
    }
}

?>
-----
<?php

namespace Tests\Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use PhpSpec\ObjectBehavior;

class BlablaTest extends \PHPUnit\Framework\TestCase
{
    private \Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\Blabla $blabla;
    protected function setUp()
    {
        parent::setUp();
        $this->blabla = new \Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\Blabla();
    }
    public function testMe()
    {
        /** @var SomeType|\PHPUnit\Framework\MockObject\MockObject $someType */
        $someType = $this->createMock(SomeType::class);
        $assignMe = $someType;
    }
}

?>
