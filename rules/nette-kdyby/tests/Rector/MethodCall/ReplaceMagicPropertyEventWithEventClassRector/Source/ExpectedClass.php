<?php

namespace Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\Fixture\Event;

final class FileManagerUploadEvent extends \Symfony\Contracts\EventDispatcher\Event
{
    private $user;
    public function __construct($user)
    {
        $this->user = $user;
    }
    public function user()
    {
        return $this->user;
    }
}
