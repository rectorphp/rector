<?php

declare (strict_types=1);
namespace Rector\Nette\FormControlTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Reflection\FunctionLikeReflectionParser;
use Rector\Core\ValueObject\MethodName;
use Rector\Nette\Contract\FormControlTypeResolverInterface;
use Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class MagicNetteFactoryInterfaceFormControlTypeResolver implements \Rector\Nette\Contract\FormControlTypeResolverInterface
{
    /**
     * @var \Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver
     */
    private $methodNamesByInputNamesResolver;
    /**
     * @var \Rector\NodeCollector\NodeCollector\NodeRepository
     */
    private $nodeRepository;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \Rector\Core\Reflection\FunctionLikeReflectionParser
     */
    private $functionLikeReflectionParser;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\NodeCollector\NodeCollector\NodeRepository $nodeRepository, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\Core\Reflection\FunctionLikeReflectionParser $functionLikeReflectionParser, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->nodeRepository = $nodeRepository;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->functionLikeReflectionParser = $functionLikeReflectionParser;
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @required
     */
    public function autowireMagicNetteFactoryInterfaceFormControlTypeResolver(\Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver $methodNamesByInputNamesResolver) : void
    {
        $this->methodNamesByInputNamesResolver = $methodNamesByInputNamesResolver;
    }
    /**
     * @return array<string, string>
     */
    public function resolve(\PhpParser\Node $node) : array
    {
        if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
            return [];
        }
        // skip constructor, handled elsewhere
        if ($this->nodeNameResolver->isName($node->name, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
            return [];
        }
        $methodName = $this->nodeNameResolver->getName($node->name);
        if ($methodName === null) {
            return [];
        }
        $classMethod = $this->nodeRepository->findClassMethodByMethodCall($node);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            $classMethod = $this->resolveReflectionClassMethod($node, $methodName);
            if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
                return [];
            }
        }
        $classReflection = $this->resolveClassReflectionByMethodCall($node);
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return [];
        }
        $returnedType = $this->nodeTypeResolver->getStaticType($node);
        if (!$returnedType instanceof \PHPStan\Type\TypeWithClassName) {
            return [];
        }
        $constructorClassMethod = $this->nodeRepository->findClassMethod($returnedType->getClassName(), \Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if (!$constructorClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            $constructorClassMethod = $this->resolveReflectionClassMethodFromClassNameAndMethod($returnedType->getClassName(), \Rector\Core\ValueObject\MethodName::CONSTRUCT);
            if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
                return [];
            }
        }
        if (!$constructorClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return [];
        }
        return $this->methodNamesByInputNamesResolver->resolveExpr($constructorClassMethod);
    }
    private function resolveReflectionClassMethod(\PhpParser\Node\Expr\MethodCall $methodCall, string $methodName) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        $classReflection = $this->resolveClassReflectionByMethodCall($methodCall);
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return null;
        }
        $methodReflection = $classReflection->getNativeMethod($methodName);
        return $this->functionLikeReflectionParser->parseMethodReflection($methodReflection);
    }
    private function resolveReflectionClassMethodFromClassNameAndMethod(string $className, string $methodName) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        $methodReflection = $classReflection->getNativeMethod($methodName);
        return $this->functionLikeReflectionParser->parseMethodReflection($methodReflection);
    }
    private function resolveClassReflectionByMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PHPStan\Reflection\ClassReflection
    {
        $callerClassName = $this->nodeRepository->resolveCallerClassName($methodCall);
        if ($callerClassName === null) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass($callerClassName)) {
            return null;
        }
        return $this->reflectionProvider->getClass($callerClassName);
    }
}
