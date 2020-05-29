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

    public function onTomatoBuy(Tomato $tomato, int $adminId): void
    {
        $tomato->unwrap();
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
        $tomato = $vegetableMarketTomatoBuyEvent->getTomato();
        $tomato->unwrap();
    }
}

?>
