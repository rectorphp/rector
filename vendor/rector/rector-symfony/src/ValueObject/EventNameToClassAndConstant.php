<?php

declare (strict_types=1);
namespace Rector\Symfony\ValueObject;

final class EventNameToClassAndConstant
{
    /**
     * @readonly
     */
    private string $eventName;
    /**
     * @readonly
     */
    private string $eventClass;
    /**
     * @readonly
     */
    private string $eventConstant;
    public function __construct(string $eventName, string $eventClass, string $eventConstant)
    {
        $this->eventName = $eventName;
        $this->eventClass = $eventClass;
        $this->eventConstant = $eventConstant;
    }
    public function getEventName() : string
    {
        return $this->eventName;
    }
    public function getEventClass() : string
    {
        return $this->eventClass;
    }
    public function getEventConstant() : string
    {
        return $this->eventConstant;
    }
}
