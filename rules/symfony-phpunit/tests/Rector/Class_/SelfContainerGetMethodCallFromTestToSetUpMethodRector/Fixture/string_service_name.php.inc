<?php

namespace Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StringServiceName extends KernelTestCase
{
    public function testOne()
    {
        $someValue = self::$container->get('some_value');
        $someValue->doStuff();
    }
}

?>
-----
<?php

namespace Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StringServiceName extends KernelTestCase
{
    private \some_value $someValue;
    protected function setUp()
    {
        parent::setUp();
        $this->someValue = self::$container->get('some_value');
    }
    public function testOne()
    {
        $this->someValue->doStuff();
    }
}

?>
