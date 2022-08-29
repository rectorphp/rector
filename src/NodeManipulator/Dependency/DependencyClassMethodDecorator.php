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
use PHPStan\Type\Type;
use Rector\Core\NodeAnalyzer\PromotedPropertyParamCleaner;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
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
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(NodeFactory $nodeFactory, PromotedPropertyParamCleaner $promotedPropertyParamCleaner, ReflectionProvider $reflectionProvider, AstResolver $astResolver, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeFactory = $nodeFactory;
        $this->promotedPropertyParamCleaner = $promotedPropertyParamCleaner;
        $this->reflectionProvider = $reflectionProvider;
        $this->astResolver = $astResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * Add "parent::__construct(X, Y, Z)" where needed
     */
    public function decorateConstructorWithParentDependencies(Class_ $class, ClassMethod $classMethod, Scope $scope) : void
    {
        $className = (string) $this->nodeNameResolver->getName($class);
        if (!$this->reflectionProvider->hasClass($className)) {
            return;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if (!$parentClassReflection->hasMethod(MethodName::CONSTRUCT)) {
                continue;
            }
            $constructorMethodReflection = $parentClassReflection->getMethod(MethodName::CONSTRUCT, $scope);
            $parentConstructorClassMethod = $this->astResolver->resolveClassMethodFromMethodReflection($constructorMethodReflection);
            if (!$parentConstructorClassMethod instanceof ClassMethod) {
                continue;
            }
            $this->completeParentConstructorBasedOnParentNode($classMethod, $parentConstructorClassMethod);
            break;
        }
    }
    private function completeParentConstructorBasedOnParentNode(ClassMethod $classMethod, ClassMethod $parentClassMethod) : void
    {
        $paramsWithoutDefaultValue = [];
        foreach ($parentClassMethod->params as $param) {
            if ($param->default !== null) {
                break;
            }
            $paramsWithoutDefaultValue[] = $param;
        }
        $cleanParams = $this->cleanParamsFromVisibilityAndAttributes($paramsWithoutDefaultValue);
        $cleanParamsToAdd = $this->removeAlreadyPresentParams($cleanParams, $classMethod->params);
        // replicate parent parameters
        if ($cleanParamsToAdd !== []) {
            $classMethod->params = \array_merge($cleanParamsToAdd, $classMethod->params);
        }
        $staticCall = $this->nodeFactory->createParentConstructWithParams($cleanParams);
        $classMethod->stmts[] = new Expression($staticCall);
    }
    /**
     * @param Param[] $params
     * @return Param[]
     */
    private function cleanParamsFromVisibilityAndAttributes(array $params) : array
    {
        $cleanParams = $this->promotedPropertyParamCleaner->cleanFromFlags($params);
        // remove deep attributes to avoid bugs with nested tokens re-print
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($cleanParams, static function (Node $node) {
            $node->setAttributes([]);
            return null;
        });
        return $cleanParams;
    }
    /**
     * @param Param[] $params
     * @param Param[] $originalParams
     * @return Param[]
     */
    private function removeAlreadyPresentParams(array $params, array $originalParams) : array
    {
        return \array_filter($params, function (Param $param) use($originalParams) : bool {
            $type = $param->type === null ? null : $this->nodeTypeResolver->getType($param->type);
            foreach ($originalParams as $originalParam) {
                if (!$this->nodeNameResolver->areNamesEqual($originalParam, $param)) {
                    continue;
                }
                $originalType = $originalParam->type === null ? null : $this->nodeTypeResolver->getType($originalParam->type);
                if (!$this->areMaybeTypesEqual($type, $originalType)) {
                    return \true;
                }
                if ($originalParam->byRef !== $param->byRef) {
                    return \true;
                }
                // All important characteristics of the type are the same, do not re-add.
                return $originalParam->variadic !== $param->variadic;
            }
            return \true;
        });
    }
    private function areMaybeTypesEqual(?Type $type1, ?Type $type2) : bool
    {
        if ($type1 === null) {
            return $type2 === null;
        }
        if ($type2 === null) {
            // Type 1 is already not null
            return \false;
        }
        return $type1->equals($type2);
    }
}
