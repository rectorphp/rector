<?php

declare (strict_types=1);
namespace Rector\Core\Reflection;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PHPStan\Reflection\TypeToCallReflectionResolver\TypeToCallReflectionResolverRegistry;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use RectorPrefix20220531\Symfony\Contracts\Service\Attribute\Required;
final class ReflectionResolver
{
    /**
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PHPStan\Reflection\TypeToCallReflectionResolver\TypeToCallReflectionResolverRegistry
     */
    private $typeToCallReflectionResolverRegistry;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PHPStan\Reflection\TypeToCallReflectionResolver\TypeToCallReflectionResolverRegistry $typeToCallReflectionResolverRegistry, \Rector\Core\NodeAnalyzer\ClassAnalyzer $classAnalyzer)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->typeToCallReflectionResolverRegistry = $typeToCallReflectionResolverRegistry;
        $this->classAnalyzer = $classAnalyzer;
    }
    /**
     * @required
     */
    public function autowire(\Rector\Core\PhpParser\AstResolver $astResolver) : void
    {
        $this->astResolver = $astResolver;
    }
    public function resolveClassAndAnonymousClass(\PhpParser\Node\Stmt\ClassLike $classLike) : \PHPStan\Reflection\ClassReflection
    {
        if ($classLike instanceof \PhpParser\Node\Stmt\Class_ && $this->classAnalyzer->isAnonymousClass($classLike)) {
            return $this->reflectionProvider->getAnonymousClassReflection($classLike, $classLike->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE));
        }
        $className = (string) $this->nodeNameResolver->getName($classLike);
        return $this->reflectionProvider->getClass($className);
    }
    public function resolveClassReflection(?\PhpParser\Node $node) : ?\PHPStan\Reflection\ClassReflection
    {
        if (!$node instanceof \PhpParser\Node) {
            return null;
        }
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        return $scope->getClassReflection();
    }
    /**
     * @param \PhpParser\Node\Expr\New_|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    public function resolveClassReflectionSourceObject($node) : ?\PHPStan\Reflection\ClassReflection
    {
        if ($node instanceof \PhpParser\Node\Expr\New_ && $node->class instanceof \PhpParser\Node\Name\FullyQualified) {
            $className = $node->class->toString();
            if ($this->reflectionProvider->hasClass($className)) {
                return $this->reflectionProvider->getClass($className);
            }
            return null;
        }
        if ($node instanceof \PhpParser\Node\Expr\MethodCall || $node instanceof \PhpParser\Node\Expr\StaticCall) {
            $classMethod = $this->astResolver->resolveClassMethodFromCall($node);
            return $this->resolveClassReflection($classMethod);
        }
        return null;
    }
    /**
     * @param class-string $className
     */
    public function resolveMethodReflection(string $className, string $methodName, ?\PHPStan\Analyser\Scope $scope) : ?\PHPStan\Reflection\MethodReflection
    {
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        // better, with support for "@method" annotation methods
        if ($scope instanceof \PHPStan\Analyser\Scope) {
            if ($classReflection->hasMethod($methodName)) {
                return $classReflection->getMethod($methodName, $scope);
            }
        } elseif ($classReflection->hasNativeMethod($methodName)) {
            return $classReflection->getNativeMethod($methodName);
        }
        return null;
    }
    public function resolveMethodReflectionFromStaticCall(\PhpParser\Node\Expr\StaticCall $staticCall) : ?\PHPStan\Reflection\MethodReflection
    {
        $objectType = $this->nodeTypeResolver->getType($staticCall->class);
        /** @var array<class-string> $classNames */
        $classNames = \PHPStan\Type\TypeUtils::getDirectClassNames($objectType);
        $methodName = $this->nodeNameResolver->getName($staticCall->name);
        if ($methodName === null) {
            return null;
        }
        $scope = $staticCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        foreach ($classNames as $className) {
            $methodReflection = $this->resolveMethodReflection($className, $methodName, $scope);
            if ($methodReflection instanceof \PHPStan\Reflection\MethodReflection) {
                return $methodReflection;
            }
        }
        return null;
    }
    public function resolveMethodReflectionFromMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PHPStan\Reflection\MethodReflection
    {
        $callerType = $this->nodeTypeResolver->getType($methodCall->var);
        if (!$callerType instanceof \PHPStan\Type\TypeWithClassName) {
            return null;
        }
        $methodName = $this->nodeNameResolver->getName($methodCall->name);
        if ($methodName === null) {
            return null;
        }
        $scope = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        return $this->resolveMethodReflection($callerType->getClassName(), $methodName, $scope);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\FuncCall $call
     * @return \PHPStan\Reflection\MethodReflection|\PHPStan\Reflection\FunctionReflection|null
     */
    public function resolveFunctionLikeReflectionFromCall($call)
    {
        if ($call instanceof \PhpParser\Node\Expr\MethodCall) {
            return $this->resolveMethodReflectionFromMethodCall($call);
        }
        if ($call instanceof \PhpParser\Node\Expr\StaticCall) {
            return $this->resolveMethodReflectionFromStaticCall($call);
        }
        return $this->resolveFunctionReflectionFromFuncCall($call);
    }
    public function resolveMethodReflectionFromClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PHPStan\Reflection\MethodReflection
    {
        $classLike = $this->betterNodeFinder->findParentType($classMethod, \PhpParser\Node\Stmt\ClassLike::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return null;
        }
        $className = $this->nodeNameResolver->getName($classLike);
        if (!\is_string($className)) {
            return null;
        }
        $methodName = $this->nodeNameResolver->getName($classMethod);
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        return $this->resolveMethodReflection($className, $methodName, $scope);
    }
    public function resolveMethodReflectionFromNew(\PhpParser\Node\Expr\New_ $new) : ?\PHPStan\Reflection\MethodReflection
    {
        $newClassType = $this->nodeTypeResolver->getType($new->class);
        if (!$newClassType instanceof \PHPStan\Type\TypeWithClassName) {
            return null;
        }
        $scope = $new->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        return $this->resolveMethodReflection($newClassType->getClassName(), \Rector\Core\ValueObject\MethodName::CONSTRUCT, $scope);
    }
    /**
     * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $propertyFetch
     */
    public function resolvePropertyReflectionFromPropertyFetch($propertyFetch) : ?\PHPStan\Reflection\Php\PhpPropertyReflection
    {
        $fetcheeType = $propertyFetch instanceof \PhpParser\Node\Expr\PropertyFetch ? $this->nodeTypeResolver->getType($propertyFetch->var) : $this->nodeTypeResolver->getType($propertyFetch->class);
        if (!$fetcheeType instanceof \PHPStan\Type\TypeWithClassName) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass($fetcheeType->getClassName())) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($fetcheeType->getClassName());
        $propertyName = $this->nodeNameResolver->getName($propertyFetch->name);
        if ($propertyName === null) {
            return null;
        }
        if (!$classReflection->hasProperty($propertyName)) {
            return null;
        }
        $scope = $propertyFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if ($scope instanceof \PHPStan\Analyser\Scope) {
            $propertyRelfection = $classReflection->getProperty($propertyName, $scope);
            if ($propertyRelfection instanceof \PHPStan\Reflection\Php\PhpPropertyReflection) {
                return $propertyRelfection;
            }
            return null;
        }
        return $classReflection->getNativeProperty($propertyName);
    }
    /**
     * @return \PHPStan\Reflection\FunctionReflection|\PHPStan\Reflection\MethodReflection|null
     */
    private function resolveFunctionReflectionFromFuncCall(\PhpParser\Node\Expr\FuncCall $funcCall)
    {
        $scope = $funcCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if ($funcCall->name instanceof \PhpParser\Node\Name) {
            if ($this->reflectionProvider->hasFunction($funcCall->name, $scope)) {
                return $this->reflectionProvider->getFunction($funcCall->name, $scope);
            }
            return null;
        }
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        // fallback to callable
        $funcCallNameType = $scope->getType($funcCall->name);
        return $this->typeToCallReflectionResolverRegistry->resolve($funcCallNameType, $scope);
    }
}
