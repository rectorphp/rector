<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\CodingStyle\Naming\ClassNaming;

final class DispatchMethodCallFactory
{
    /**
     * @var ClassNaming
     */
    private $classNaming;

    public function __construct(ClassNaming $classNaming)
    {
        $this->classNaming = $classNaming;
    }

    public function createFromEventClassName(string $eventClassName): MethodCall
    {
        $shortEventClassName = $this->classNaming->getVariableName($eventClassName);

        $eventDispatcherPropertyFetch = new PropertyFetch(new Variable('this'), 'eventDispatcher');
        $dispatchMethodCall = new MethodCall($eventDispatcherPropertyFetch, 'dispatch');
        $dispatchMethodCall->args[] = new Arg(new Variable($shortEventClassName));

        return $dispatchMethodCall;
    }
}
