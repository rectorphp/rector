<?php

namespace Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\Fixture;

use Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\Source\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SomeTest extends KernelTestCase
{
    public function testOne()
    {
        $itemRepository = self::$container->get(ItemRepository::class);
        $itemRepository->doStuff();
    }

    public function testTwo()
    {
        $itemRepository = self::$container->get(ItemRepository::class);
        $itemRepository->doAnotherStuff();
    }
}

?>
-----
<?php

namespace Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\Fixture;

use Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\Source\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SomeTest extends KernelTestCase
{
    private \Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\Source\ItemRepository $itemRepository;
    protected function setUp()
    {
        parent::setUp();
        $this->itemRepository = self::$container->get(\Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\Source\ItemRepository::class);
    }
    public function testOne()
    {
        $this->itemRepository->doStuff();
    }

    public function testTwo()
    {
        $this->itemRepository->doAnotherStuff();
    }
}

?>
