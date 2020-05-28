<?php

namespace Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\Fixture;

use Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\Source\SomeUser;

final class DuplicatedEventParams
{
    public $onUpload;

    public function run(SomeUser $user)
    {
        $this->onUpload($user['owner_id'], $user['name']);
    }
}

?>
-----
<?php

namespace Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\Fixture;

use Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\Source\SomeUser;

final class DuplicatedEventParams
{
    /**
     * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;
    public function __construct(\Symfony\Contracts\EventDispatcher\EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
    public function run(SomeUser $user)
    {
        $duplicatedEventParamsUploadEvent = new \Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\Fixture\Event\DuplicatedEventParamsUploadEvent($user['owner_id'], $user['name']);
        $this->eventDispatcher->dispatch($duplicatedEventParamsUploadEvent);
    }
}

?>
