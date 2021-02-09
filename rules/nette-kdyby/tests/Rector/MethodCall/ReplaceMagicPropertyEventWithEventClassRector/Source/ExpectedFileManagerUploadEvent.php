<?php

namespace Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\Fixture\Event;

final class FileManagerUploadEvent extends \Symfony\Contracts\EventDispatcher\Event
{
    private \Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\Source\SomeUser $user;
    public function __construct(\Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\Source\SomeUser $user)
    {
        $this->user = $user;
    }
    public function getUser(): \Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\Source\SomeUser
    {
        return $this->user;
    }
}
