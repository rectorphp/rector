<?php

namespace Rector\NetteKdyby\Tests\Rector\ClassMethod\ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRector\Fixture;

use Kdyby\Events\Subscriber;
use Rector\NetteKdyby\Tests\Rector\ClassMethod\ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRector\Source\Tomato;
use Rector\NetteKdyby\Tests\Rector\ClassMethod\ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRector\Source\VegetableMarket;

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

namespace Rector\NetteKdyby\Tests\Rector\ClassMethod\ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRector\Fixture;

use Kdyby\Events\Subscriber;
use Rector\NetteKdyby\Tests\Rector\ClassMethod\ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRector\Source\Tomato;
use Rector\NetteKdyby\Tests\Rector\ClassMethod\ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRector\Source\VegetableMarket;

final class ActionLogEventSubscriber implements Subscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            \Rector\NetteKdyby\Tests\Rector\ClassMethod\ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRector\Source\Event\VegetableMarketTomatoBuyEvent::class => 'onTomatoBuy',
        ];
    }

    public function onTomatoBuy(\Rector\NetteKdyby\Tests\Rector\ClassMethod\ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRector\Source\Event\VegetableMarketTomatoBuyEvent $vegetableMarketTomatoBuyEvent): void
    {
        $anotherTomato = $vegetableMarketTomatoBuyEvent->getTomato();
        $anotherTomato->unwrap();
    }
}

?>
