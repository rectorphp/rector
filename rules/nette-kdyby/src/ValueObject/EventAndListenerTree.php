<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\ValueObject;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;

final class EventAndListenerTree
{
    /**
     * @var string
     */
    private $eventClassName;

    /**
     * @var ClassMethod[][]
     */
    private $listenerClassMethodsByEventSubscriberClass = [];

    /**
     * @var Property|null
     */
    private $onMagicProperty;

    /**
     * @var string
     */
    private $eventFileLocation;

    /**
     * @var MethodCall
     */
    private $magicDispatchMethodCall;

    /**
     * @var Namespace_
     */
    private $eventClassInNamespace;

    /**
     * @var MethodCall
     */
    private $eventDispatcherDispatchMethodCall;

    /**
     * @var GetterMethodBlueprint[]
     */
    private $getterMethodBlueprints = [];

    /**
     * @param ClassMethod[][] $listenerClassMethodsByEventSubscriberClass
     */
    public function __construct(
        MethodCall $magicDispatchMethodCall,
        ?Property $onMagicProperty,
        string $eventClassName,
        string $eventFileLocation,
        Namespace_ $eventClassInNamespace,
        MethodCall $eventDispatcherDispatchMethodCall,
        array $listenerClassMethodsByEventSubscriberClass,
        array $getterMethodsBlueprints
    ) {
        $this->magicDispatchMethodCall = $magicDispatchMethodCall;
        $this->onMagicProperty = $onMagicProperty;
        $this->eventClassName = $eventClassName;
        $this->eventFileLocation = $eventFileLocation;
        $this->eventClassInNamespace = $eventClassInNamespace;
        $this->listenerClassMethodsByEventSubscriberClass = $listenerClassMethodsByEventSubscriberClass;
        $this->eventDispatcherDispatchMethodCall = $eventDispatcherDispatchMethodCall;
        $this->getterMethodBlueprints = $getterMethodsBlueprints;
    }

    public function getEventClassName(): string
    {
        return $this->eventClassName;
    }

    /**
     * @return array<string, ClassMethod>
     */
    public function getListenerClassMethodsByClass(string $className): array
    {
        return $this->listenerClassMethodsByEventSubscriberClass[$className] ?? [];
    }

    public function getOnMagicProperty(): ?Property
    {
        return $this->onMagicProperty;
    }

    public function getEventFileLocation(): string
    {
        return $this->eventFileLocation;
    }

    public function getMagicDispatchMethodCall(): MethodCall
    {
        return $this->magicDispatchMethodCall;
    }

    public function getEventClassInNamespace(): Namespace_
    {
        return $this->eventClassInNamespace;
    }

    public function getEventDispatcherDispatchMethodCall(): MethodCall
    {
        return $this->eventDispatcherDispatchMethodCall;
    }

    /**
     * @return GetterMethodBlueprint[]
     */
    public function getGetterMethodBlueprints(): array
    {
        return $this->getterMethodBlueprints;
    }
}
