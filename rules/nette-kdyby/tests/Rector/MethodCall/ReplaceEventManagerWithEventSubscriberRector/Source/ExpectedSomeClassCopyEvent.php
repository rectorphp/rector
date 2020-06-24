<?php

namespace Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector\Fixture\Event;

final class SomeClassCopyEvent extends \Symfony\Contracts\EventDispatcher\Event
{
    private \Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector\Fixture\SomeClass $someClass;
    private string $key;
    public function __construct(\Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector\Fixture\SomeClass $someClass, string $key)
    {
        $this->someClass = $someClass;
        $this->key = $key;
    }
    public function getSomeClass(): \Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector\Fixture\SomeClass
    {
        return $this->someClass;
    }
    public function getKey(): string
    {
        return $this->key;
    }
}
