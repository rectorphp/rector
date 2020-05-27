<?php

namespace Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\Fixture;

final class FileManager
{
    public $onUpload;

    public function run(User $user)
    {
        $this->onUpload($user);
    }
}

?>
-----
<?php

namespace Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\Fixture;

final class FileManager
{
    /**
     * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;
    public function __construct(\Symfony\Contracts\EventDispatcher\EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
    public function run(User $user)
    {
        $fileManagerUploadEvent = new \Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\Fixture\Event\FileManagerUploadEvent($user);
        $this->eventDispatcher->dispatch($fileManagerUploadEvent);
    }
}

?>
