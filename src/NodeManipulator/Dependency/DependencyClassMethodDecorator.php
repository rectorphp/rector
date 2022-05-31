<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator\Dependency;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\NodeAnalyzer\PromotedPropertyParamCleaner;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220531\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class DependencyClassMethodDecorator
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PromotedPropertyParamCleaner
     */
    private $promotedPropertyParamCleaner;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\Core\NodeAnalyzer\PromotedPropertyParamCleaner $promotedPropertyParamCleaner, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\Core\PhpParser\AstResolver $astResolver, \RectorPrefix20220531\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeFactory = $nodeFactory;
        $this->promotedPropertyParamCleaner = $promotedPropertyParamCleaner;
        $this->reflectionProvider = $reflectionProvider;
        $this->astResolver = $astResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * Add "parent::__construct(X, Y, Z)" where needed
     */
    public function decorateConstructorWithParentDependencies(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Stmt\ClassMethod $classMethod, \PHPStan\Analyser\Scope $scope) : void
    {
        $className = (string) $this->nodeNameResolver->getName($class);
        if (!$this->reflectionProvider->hasClass($className)) {
            return;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if (!$parentClassReflection->hasMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
                continue;
            }
            $constructorMethodReflection = $parentClassReflection->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT, $scope);
            $parentConstructorClassMethod = $this->astResolver->resolveClassMethodFromMethodReflection($constructorMethodReflection);
            if (!$parentConstructorClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
                continue;
            }
            $this->completeParentConstructorBasedOnParentNode($classMethod, $parentConstructorClassMethod);
            break;
        }
    }
    private function completeParentConstructorBasedOnParentNode(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Stmt\ClassMethod $parentClassMethod) : void
    {
        $paramsWithoutDefaultValue = [];
        foreach ($parentClassMethod->params as $param) {
            if ($param->default !== null) {
                break;
            }
            $paramsWithoutDefaultValue[] = $param;
        }
        $cleanParams = $this->cleanParamsFromVisibilityAndAttributes($paramsWithoutDefaultValue);
        // replicate parent parameters
        if ($cleanParams !== []) {
            $classMethod->params = \array_merge($cleanParams, $classMethod->params);
        }
        $staticCall = $this->nodeFactory->createParentConstructWithParams($cleanParams);
        $classMethod->stmts[] = new \PhpParser\Node\Stmt\Expression($staticCall);
    }
    /**
     * @param Param[] $params
     * @return Param[]
     */
    private function cleanParamsFromVisibilityAndAttributes(array $params) : array
    {
        $cleanParams = $this->promotedPropertyParamCleaner->cleanFromFlags($params);
        // remove deep attributes to avoid bugs with nested tokens re-print
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($cleanParams, function (\PhpParser\Node $node) {
            $node->setAttributes([]);
            return null;
        });
        return $cleanParams;
    }
}
