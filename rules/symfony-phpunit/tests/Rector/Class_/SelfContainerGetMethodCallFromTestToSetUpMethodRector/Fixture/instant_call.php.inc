<?php

namespace Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\Source\ItemRepository;

class InstantCall extends KernelTestCase
{
    public function testOne()
    {
        $item = self::$container->get(ItemRepository::class)->get(1);
    }

    public function testTwo()
    {
        $item = self::$container->get(ItemRepository::class)->get(2);
    }
}

?>
-----
<?php

namespace Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\Source\ItemRepository;

class InstantCall extends KernelTestCase
{
    private \Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\Source\ItemRepository $itemRepository;
    protected function setUp()
    {
        parent::setUp();
        $this->itemRepository = self::$container->get(\Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\Source\ItemRepository::class);
    }
    public function testOne()
    {
        $item = $this->itemRepository->get(1);
    }

    public function testTwo()
    {
        $item = $this->itemRepository->get(2);
    }
}

?>
