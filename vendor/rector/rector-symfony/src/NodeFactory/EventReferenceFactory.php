<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Scalar\String_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Symfony\ValueObject\EventNameToClassAndConstant;
final class EventReferenceFactory
{
    /**
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->nodeFactory = $nodeFactory;
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @param EventNameToClassAndConstant[] $eventNamesToClassConstants
     * @return String_|ClassConstFetch
     */
    public function createEventName(string $eventName, array $eventNamesToClassConstants) : \PhpParser\Node
    {
        if ($this->reflectionProvider->hasClass($eventName)) {
            return $this->nodeFactory->createClassConstReference($eventName);
        }
        // is string a that could be caught in constant, e.g. KernelEvents?
        foreach ($eventNamesToClassConstants as $eventNameToClassConstant) {
            if ($eventNameToClassConstant->getEventName() !== $eventName) {
                continue;
            }
            return $this->nodeFactory->createClassConstFetch($eventNameToClassConstant->getEventClass(), $eventNameToClassConstant->getEventConstant());
        }
        return new \PhpParser\Node\Scalar\String_($eventName);
    }
}
