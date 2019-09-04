<?php

namespace Rector\SymfonyPHPUnit\Tests\Rector\Class_\MultipleServiceGetToSetUpMethodRector\Fixture;

use Rector\SymfonyPHPUnit\Tests\Rector\Class_\MultipleServiceGetToSetUpMethodRector\Source\DummyKernelTestCase;
use Rector\SymfonyPHPUnit\Tests\Rector\Class_\MultipleServiceGetToSetUpMethodRector\Source\ItemRepository;

class InstantCall extends DummyKernelTestCase
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

namespace Rector\SymfonyPHPUnit\Tests\Rector\Class_\MultipleServiceGetToSetUpMethodRector\Fixture;

use Rector\SymfonyPHPUnit\Tests\Rector\Class_\MultipleServiceGetToSetUpMethodRector\Source\DummyKernelTestCase;
use Rector\SymfonyPHPUnit\Tests\Rector\Class_\MultipleServiceGetToSetUpMethodRector\Source\ItemRepository;

class InstantCall extends DummyKernelTestCase
{
    /**
     * @var \Rector\SymfonyPHPUnit\Tests\Rector\Class_\MultipleServiceGetToSetUpMethodRector\Source\ItemRepository
     */
    private $itemRepository;
    protected function setUp()
    {
        parent::setUp();
        $this->itemRepository = self::$container->get(\Rector\SymfonyPHPUnit\Tests\Rector\Class_\MultipleServiceGetToSetUpMethodRector\Source\ItemRepository::class);
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
