<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\RemoveExpectAnyFromMockRector\Fixture;

use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function test()
    {
        $translator = $this->createMock('SeeSomeClass');
        $translator->expects($this->any())
            ->method('trans')
            ->willReturn('translated max {{ max }}!');
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\RemoveExpectAnyFromMockRector\Fixture;

use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function test()
    {
        $translator = $this->createMock('SeeSomeClass');
        $translator
            ->method('trans')
            ->willReturn('translated max {{ max }}!');
    }
}

?>
