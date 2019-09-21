<?php

namespace Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\Fixture;

use Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\Source\DummyKernelTestCase;

class StringServiceName extends DummyKernelTestCase
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

use Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\Source\DummyKernelTestCase;

class StringServiceName extends DummyKernelTestCase
{
    /**
     * @var some_value
     */
    private $someValue;
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
