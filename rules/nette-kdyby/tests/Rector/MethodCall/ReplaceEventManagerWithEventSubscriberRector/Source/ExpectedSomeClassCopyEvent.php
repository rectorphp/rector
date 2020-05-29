<?php

namespace Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector\Fixture\Event;

final class SomeClassCopyEvent extends \Symfony\Contracts\EventDispatcher\Event
{
    /**
     * @var $this
     */
    private $this;
    /**
     * @var string
     */
    private $key;
    public function __construct(static $this, string $key)
    {
        $this->this = $this;
        $this->key = $key;
    }
    public function getThis(): static
    {
        return $this->this;
    }
    public function getKey(): string
    {
        return $this->key;
    }
}
