<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\UseSpecificWillMethodRector\Fixture;

class SomeClass extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')->getMock();
        $translator->expects($this->any())
            ->method('trans')
            ->will($this->returnValue('translated max {{ max }}!'));

        $translator->expects($this->any())
            ->will($this->returnSelf());

        $translator->expects($this->any())
            ->will($this->returnArgument(2));

        $translator->expects($this->any())
            ->will($this->returnCallback("str_rot13"));

        $translator->expects($this->any())
            ->will($this->returnValueMap(["a", "b", "c", "d"]));

        $translator->expects($this->any())
            ->WILL($this->returnValue(1));

        $translator->expects($this->any())
            ->will($this->ReturnVALUE(2));

        $translator->expects($this->any())
            ->will($this->throwException(new \InvalidArgumentException()));
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\UseSpecificWillMethodRector\Fixture;

class SomeClass extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')->getMock();
        $translator->expects($this->any())
            ->method('trans')
            ->willReturn('translated max {{ max }}!');

        $translator->expects($this->any())->willReturnSelf();

        $translator->expects($this->any())
            ->willReturnArgument(2);

        $translator->expects($this->any())
            ->willReturnCallback("str_rot13");

        $translator->expects($this->any())
            ->willReturnMap(["a", "b", "c", "d"]);

        $translator->expects($this->any())
            ->willReturn(1);

        $translator->expects($this->any())
            ->willReturn(2);

        $translator->expects($this->any())
            ->willThrowException(new \InvalidArgumentException());
    }
}

?>
