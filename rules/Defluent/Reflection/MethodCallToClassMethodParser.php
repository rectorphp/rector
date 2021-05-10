<?php

declare (strict_types=1);
namespace Rector\Defluent\Reflection;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Reflection\FunctionLikeReflectionParser;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class MethodCallToClassMethodParser
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var FunctionLikeReflectionParser
     */
    private $functionLikeReflectionParser;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\Core\Reflection\FunctionLikeReflectionParser $functionLikeReflectionParser)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->functionLikeReflectionParser = $functionLikeReflectionParser;
    }
    public function parseMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        $callerStaticType = $this->nodeTypeResolver->getStaticType($methodCall->var);
        if (!$callerStaticType instanceof \PHPStan\Type\TypeWithClassName) {
            return null;
        }
        $callerClassReflection = $this->reflectionProvider->getClass($callerStaticType->getClassName());
        $methodName = $this->nodeNameResolver->getName($methodCall->name);
        if ($methodName === null) {
            return null;
        }
        $methodReflection = $callerClassReflection->getNativeMethod($methodName);
        return $this->functionLikeReflectionParser->parseMethodReflection($methodReflection);
    }
}
