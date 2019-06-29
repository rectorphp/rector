<?php

namespace Rector\SymfonyPHPUnit\Tests\Rector\Class_\MultipleServiceGetToSetUpMethodRector\Fixture;

use ItemRepository;
use Rector\SymfonyPHPUnit\Tests\Rector\Class_\MultipleServiceGetToSetUpMethodRector\Source\DummyKernelTestCase;

class StringServiceName extends DummyKernelTestCase
{
    public function testOne()
    {
        $someValue = self::$container->get('some_value');
        $someValue->doStuff();
    }

    public function testTwo()
    {
        $someValue = self::$container->get('some_value');
        $someValue->doAnotherStuff();
    }
}

?>
-----
<?php

namespace Rector\SymfonyPHPUnit\Tests\Rector\Class_\MultipleServiceGetToSetUpMethodRector\Fixture;

use ItemRepository;
use Rector\SymfonyPHPUnit\Tests\Rector\Class_\MultipleServiceGetToSetUpMethodRector\Source\DummyKernelTestCase;

class StringServiceName extends DummyKernelTestCase
{
    /**
     * @var \some_value
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

    public function testTwo()
    {
        $this->someValue->doAnotherStuff();
    }
}

?>
