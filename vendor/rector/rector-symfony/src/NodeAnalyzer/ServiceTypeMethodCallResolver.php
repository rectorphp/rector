<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Symfony\DataProvider\ServiceMapProvider;
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
