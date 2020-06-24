<?php

namespace Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\Source\ItemRepository;

class ExistingSetUpTest extends KernelTestCase
{
    protected function setUp(): void
    {
        $value = 5;
    }

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

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\Source\ItemRepository;

class ExistingSetUpTest extends KernelTestCase
{
    private \Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\Source\ItemRepository $itemRepository;
    protected function setUp(): void
    {
        $value = 5;
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
