<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Expr\ClassConstFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Rector\Symfony\DataProvider\ServiceMapProvider;
final class ServiceTypeMethodCallResolver
{
    /**
     * @readonly
     * @var \Rector\Symfony\DataProvider\ServiceMapProvider
     */
    private $serviceMapProvider;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(ServiceMapProvider $serviceMapProvider, NodeNameResolver $nodeNameResolver)
    {
        $this->serviceMapProvider = $serviceMapProvider;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function resolve(MethodCall $methodCall) : ?Type
    {
        if (!isset($methodCall->args[0])) {
            return new MixedType();
        }
        $argument = $methodCall->getArgs()[0]->value;
        $serviceMap = $this->serviceMapProvider->provide();
        if ($argument instanceof String_) {
            return $serviceMap->getServiceType($argument->value);
        }
        if ($argument instanceof ClassConstFetch && $argument->class instanceof Name) {
            $className = $this->nodeNameResolver->getName($argument->class);
            return new ObjectType($className);
        }
        return new MixedType();
    }
}
