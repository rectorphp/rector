<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\UseSpecificWillMethodRector\Fixture;

class With extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')->getMock();
        $translator->expects($this->any())
            ->method('trans')
            ->with($this->equalTo('old max {{ max }}!'))
            ->with($this->equalTo($log), $this->equalTo($parameters));
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\UseSpecificWillMethodRector\Fixture;

class With extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')->getMock();
        $translator->expects($this->any())
            ->method('trans')
            ->with('old max {{ max }}!')
            ->with($log, $parameters);
    }
}

?>
