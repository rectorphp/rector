<?php

declare (strict_types=1);
namespace Rector\Nette\Kdyby\ValueObject;

use PhpParser\Node\Stmt\ClassMethod;
final class EventClassAndClassMethod
{
    /**
     * @readonly
     * @var string
     */
    private $eventClass;
    /**
     * @readonly
     * @var \PhpParser\Node\Stmt\ClassMethod
     */
    private $classMethod;
    public function __construct(string $eventClass, ClassMethod $classMethod)
    {
        $this->eventClass = $eventClass;
        $this->classMethod = $classMethod;
    }
    public function getEventClass() : string
    {
        return $this->eventClass;
    }
    public function getClassMethod() : ClassMethod
    {
        return $this->classMethod;
    }
}
