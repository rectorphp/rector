<?php

declare (strict_types=1);
namespace Rector\Nette\Kdyby\ValueObject;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
final class EventAndListenerTree
{
    /**
     * @var \PhpParser\Node\Expr\MethodCall
     */
    private $magicDispatchMethodCall;
    /**
     * @var \PhpParser\Node\Stmt\Property|null
     */
    private $onMagicProperty;
    /**
     * @var string
     */
    private $eventClassName;
    /**
     * @var string
     */
    private $eventFileLocation;
    /**
     * @var \PhpParser\Node\Stmt\Namespace_
     */
    private $eventClassInNamespace;
    /**
     * @var \PhpParser\Node\Expr\MethodCall
     */
    private $eventDispatcherDispatchMethodCall;
    /**
     * @var array<class-string, \PhpParser\Node\Stmt\ClassMethod[]>
     */
    private $listenerMethodsByEventSubscriberClass;
    /**
     * @var \Rector\Nette\Kdyby\ValueObject\GetterMethodBlueprint[]
     */
    private $getterMethodBlueprints;
    /**
     * @param array<class-string, ClassMethod[]> $listenerMethodsByEventSubscriberClass
     * @param GetterMethodBlueprint[] $getterMethodBlueprints
     */
    public function __construct(\PhpParser\Node\Expr\MethodCall $magicDispatchMethodCall, ?\PhpParser\Node\Stmt\Property $onMagicProperty, string $eventClassName, string $eventFileLocation, \PhpParser\Node\Stmt\Namespace_ $eventClassInNamespace, \PhpParser\Node\Expr\MethodCall $eventDispatcherDispatchMethodCall, array $listenerMethodsByEventSubscriberClass, array $getterMethodBlueprints)
    {
        $this->magicDispatchMethodCall = $magicDispatchMethodCall;
        $this->onMagicProperty = $onMagicProperty;
        $this->eventClassName = $eventClassName;
        $this->eventFileLocation = $eventFileLocation;
        $this->eventClassInNamespace = $eventClassInNamespace;
        $this->eventDispatcherDispatchMethodCall = $eventDispatcherDispatchMethodCall;
        $this->listenerMethodsByEventSubscriberClass = $listenerMethodsByEventSubscriberClass;
        $this->getterMethodBlueprints = $getterMethodBlueprints;
    }
    public function getEventClassName() : string
    {
        return $this->eventClassName;
    }
    /**
     * @return ClassMethod[]
     */
    public function getListenerClassMethodsByClass(string $className) : array
    {
        return $this->listenerMethodsByEventSubscriberClass[$className] ?? [];
    }
    public function getOnMagicProperty() : ?\PhpParser\Node\Stmt\Property
    {
        return $this->onMagicProperty;
    }
    public function getEventFileLocation() : string
    {
        return $this->eventFileLocation;
    }
    public function getMagicDispatchMethodCall() : \PhpParser\Node\Expr\MethodCall
    {
        return $this->magicDispatchMethodCall;
    }
    public function getEventClassInNamespace() : \PhpParser\Node\Stmt\Namespace_
    {
        return $this->eventClassInNamespace;
    }
    public function getEventDispatcherDispatchMethodCall() : \PhpParser\Node\Expr\MethodCall
    {
        return $this->eventDispatcherDispatchMethodCall;
    }
    /**
     * @return GetterMethodBlueprint[]
     */
    public function getGetterMethodBlueprints() : array
    {
        return $this->getterMethodBlueprints;
    }
}
