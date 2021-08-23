<?php

declare (strict_types=1);
namespace Rector\Nette\Kdyby\ValueObject;

use PhpParser\Node\Stmt\ClassMethod;
final class EventClassAndClassMethod
{
    /**
     * @var string
     */
    private $eventClass;
    /**
     * @var \PhpParser\Node\Stmt\ClassMethod
     */
    private $classMethod;
    public function __construct(string $eventClass, \PhpParser\Node\Stmt\ClassMethod $classMethod)
    {
        $this->eventClass = $eventClass;
        $this->classMethod = $classMethod;
    }
    public function getEventClass() : string
    {
        return $this->eventClass;
    }
    public function getClassMethod() : \PhpParser\Node\Stmt\ClassMethod
    {
        return $this->classMethod;
    }
}
