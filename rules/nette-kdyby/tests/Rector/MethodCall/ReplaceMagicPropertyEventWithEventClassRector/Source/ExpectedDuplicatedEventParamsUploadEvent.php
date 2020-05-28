<?php

namespace Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\Fixture\Event;

final class DuplicatedEventParamsUploadEvent extends \Symfony\Contracts\EventDispatcher\Event
{
    /**
     * @var mixed
     */
    private $userOwnerId;
    /**
     * @var mixed
     */
    private $userName;
    public function __construct($userOwnerId, $userName)
    {
        $this->userOwnerId = $userOwnerId;
        $this->userName = $userName;
    }
    public function getUserOwnerId()
    {
        return $this->userOwnerId;
    }
    public function getUserName()
    {
        return $this->userName;
    }
}
