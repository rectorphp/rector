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
    /**
     * @var string
     */
    private $someUnderscore;
    public function __construct($userOwnerId, $userName, string $someUnderscore)
    {
        $this->userOwnerId = $userOwnerId;
        $this->userName = $userName;
        $this->someUnderscore = $someUnderscore;
    }
    public function getUserOwnerId()
    {
        return $this->userOwnerId;
    }
    public function getUserName()
    {
        return $this->userName;
    }
    public function getSomeUnderscore(): string
    {
        return $this->someUnderscore;
    }
}
