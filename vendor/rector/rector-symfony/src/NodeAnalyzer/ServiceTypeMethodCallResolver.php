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
     * @var \Rector\Symfony\DataProvider\ServiceMapProvider
     */
    private $serviceMapProvider;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\Symfony\DataProvider\ServiceMapProvider $serviceMapProvider, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->serviceMapProvider = $serviceMapProvider;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function resolve(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PHPStan\Type\Type
    {
        if (!isset($methodCall->args[0])) {
            return new \PHPStan\Type\MixedType();
        }
        $argument = $methodCall->args[0]->value;
        $serviceMap = $this->serviceMapProvider->provide();
        if ($argument instanceof \PhpParser\Node\Scalar\String_) {
            return $serviceMap->getServiceType($argument->value);
        }
        if ($argument instanceof \PhpParser\Node\Expr\ClassConstFetch && $argument->class instanceof \PhpParser\Node\Name) {
            $className = $this->nodeNameResolver->getName($argument->class);
            return new \PHPStan\Type\ObjectType($className);
        }
        return new \PHPStan\Type\MixedType();
    }
}
