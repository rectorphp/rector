<?php

namespace Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\Fixture\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\Source\SomeUser;
final class FileManagerUploadEvent extends Event
{
    private SomeUser $user;
    public function __construct(SomeUser $user)
    {
        $this->user = $user;
    }
    public function getUser(): SomeUser
    {
        return $this->user;
    }
}
