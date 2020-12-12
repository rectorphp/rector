<?php

namespace Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector\Fixture;

use Kdyby\Events\EventManager;

final class SomeClass
{
    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    public function run()
    {
        $key = '2000';
        $this->eventManager->dispatchEvent(static::class . '::onCopy', new EventArgsList([$this, $key]));
    }
}

?>
-----
<?php

namespace Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector\Fixture;

use Kdyby\Events\EventManager;

final class SomeClass
{
    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    public function run()
    {
        $key = '2000';
        $this->eventManager->dispatch(new \Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector\Fixture\Event\SomeClassCopyEvent($this, $key));
    }
}

?>
