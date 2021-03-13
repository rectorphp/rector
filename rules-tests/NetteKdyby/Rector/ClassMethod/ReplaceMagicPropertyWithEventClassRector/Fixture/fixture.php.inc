<?php

namespace Rector\Tests\NetteKdyby\Rector\ClassMethod\ReplaceMagicPropertyWithEventClassRector\Fixture;

use Kdyby\Events\Subscriber;
use Rector\Tests\NetteKdyby\Rector\ClassMethod\ReplaceMagicPropertyWithEventClassRector\Source\Tomato;

class VegetableMarket
{
    public $onTomatoBuy;

    public function run(Tomato $tomato, int $userId)
    {
        $this->onTomatoBuy($tomato, $userId);
    }
}

final class ActionLogEventSubscriber implements Subscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            VegetableMarket::class . '::onTomatoBuy' => 'onTomatoBuy',
        ];
    }

    public function onTomatoBuy(Tomato $anotherTomato, int $adminId): void
    {
        $anotherTomato->unwrap();
    }
}

?>
-----
<?php

namespace Rector\Tests\NetteKdyby\Rector\ClassMethod\ReplaceMagicPropertyWithEventClassRector\Fixture;

use Kdyby\Events\Subscriber;
use Rector\Tests\NetteKdyby\Rector\ClassMethod\ReplaceMagicPropertyWithEventClassRector\Source\Tomato;

class VegetableMarket
{
    public $onTomatoBuy;

    public function run(Tomato $tomato, int $userId)
    {
        $this->onTomatoBuy($tomato, $userId);
    }
}

final class ActionLogEventSubscriber implements Subscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            \Rector\Tests\NetteKdyby\Rector\ClassMethod\ReplaceMagicPropertyWithEventClassRector\Fixture\Event\VegetableMarketTomatoBuyEvent::class => 'onTomatoBuy',
        ];
    }

    public function onTomatoBuy(\Rector\Tests\NetteKdyby\Rector\ClassMethod\ReplaceMagicPropertyWithEventClassRector\Fixture\Event\VegetableMarketTomatoBuyEvent $vegetableMarketTomatoBuyEvent): void
    {
        $anotherTomato = $vegetableMarketTomatoBuyEvent->getTomato();
        $anotherTomato->unwrap();
    }
}

?>
