<?php

namespace Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\Fixture\Event;

final class DuplicatedEventParamsUploadEvent extends \Symfony\Contracts\EventDispatcher\Event
{
    /**
     * @var mixed
     */
    private $userId;
    /**
     * @var mixed
     */
    private $userName;
    public function __construct($userId, $userName)
    {
        $this->userId = $userId;
        $this->userName = $userName;
    }
    public function getUserId()
    {
        return $this->userId;
    }
    public function getUserName()
    {
        return $this->userName;
    }
}
