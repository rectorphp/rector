<?php

declare (strict_types=1);
namespace Rector\Nette\Kdyby\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\CodingStyle\Naming\ClassNaming;
final class DispatchMethodCallFactory
{
    /**
     * @var \Rector\CodingStyle\Naming\ClassNaming
     */
    private $classNaming;
    public function __construct(\Rector\CodingStyle\Naming\ClassNaming $classNaming)
    {
        $this->classNaming = $classNaming;
    }
    public function createFromEventClassName(string $eventClassName) : \PhpParser\Node\Expr\MethodCall
    {
        $shortEventClassName = $this->classNaming->getVariableName($eventClassName);
        $eventDispatcherPropertyFetch = new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable('this'), 'eventDispatcher');
        $dispatchMethodCall = new \PhpParser\Node\Expr\MethodCall($eventDispatcherPropertyFetch, 'dispatch');
        $dispatchMethodCall->args[] = new \PhpParser\Node\Arg(new \PhpParser\Node\Expr\Variable($shortEventClassName));
        return $dispatchMethodCall;
    }
}
