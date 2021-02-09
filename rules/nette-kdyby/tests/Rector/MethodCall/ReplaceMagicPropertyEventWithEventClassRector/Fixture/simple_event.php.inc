<?php

namespace Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\Fixture;

use Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\Source\SomeUser;

final class FileManager
{
    public $onUpload;

    public function run(SomeUser $user)
    {
        $this->onUpload($user);
    }
}

?>
-----
<?php

namespace Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\Fixture;

use Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\Source\SomeUser;

final class FileManager
{
    public function __construct(private \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $eventDispatcher)
    {
    }

    public function run(SomeUser $user)
    {
        $fileManagerUploadEvent = new \Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\Fixture\Event\FileManagerUploadEvent($user);
        $this->eventDispatcher->dispatch($fileManagerUploadEvent);
    }
}

?>
