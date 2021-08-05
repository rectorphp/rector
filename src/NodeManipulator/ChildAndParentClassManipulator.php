<?php

declare(strict_types=1);

namespace Rector\Core\NodeManipulator;

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
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class ChildAndParentClassManipulator
{
    public function __construct(
        private NodeFactory $nodeFactory,
        private NodeNameResolver $nodeNameResolver,
        private NodeRepository $nodeRepository,
        private PromotedPropertyParamCleaner $promotedPropertyParamCleaner,
        private ReflectionProvider $reflectionProvider,
        private AstResolver $astResolver,
        private SimpleCallableNodeTraverser $simpleCallableNodeTraverser
    ) {
    }

    /**
     * Add "parent::__construct(X, Y, Z)" where needed
     */
    public function completeParentConstructor(Class_ $class, ClassMethod $classMethod, Scope $scope): void
    {
        $className = $this->nodeNameResolver->getName($class);
        if ($className === null) {
            return;
        }

        if (! $this->reflectionProvider->hasClass($className)) {
            return;
        }

        $classReflection = $this->reflectionProvider->getClass($className);

        foreach ($classReflection->getParents() as $parentClassReflection) {
            if (! $parentClassReflection->hasMethod(MethodName::CONSTRUCT)) {
                continue;
            }

            $constructorMethodReflection = $parentClassReflection->getMethod(MethodName::CONSTRUCT, $scope);
            $parentConstructorClassMethod = $this->astResolver->resolveClassMethodFromMethodReflection(
                $constructorMethodReflection
            );

            if (! $parentConstructorClassMethod instanceof ClassMethod) {
                continue;
            }

            $this->completeParentConstructorBasedOnParentNode($classMethod, $parentConstructorClassMethod);

            break;
        }
    }

    public function completeChildConstructors(Class_ $class, ClassMethod $constructorClassMethod): void
    {
        $className = $this->nodeNameResolver->getName($class);
        if ($className === null) {
            return;
        }

        $childClasses = $this->nodeRepository->findChildrenOfClass($className);

        foreach ($childClasses as $childClass) {
            $childConstructorClassMethod = $childClass->getMethod(MethodName::CONSTRUCT);
            if (! $childConstructorClassMethod instanceof ClassMethod) {
                continue;
            }

            // replicate parent parameters
            $childConstructorClassMethod->params = array_merge(
                $constructorClassMethod->params,
                $childConstructorClassMethod->params
            );

            $parentConstructCallNode = $this->nodeFactory->createParentConstructWithParams(
                $constructorClassMethod->params
            );

            $childConstructorClassMethod->stmts = array_merge(
                [new Expression($parentConstructCallNode)],
                (array) $childConstructorClassMethod->stmts
            );
        }
    }

    private function completeParentConstructorBasedOnParentNode(
        ClassMethod $classMethod,
        ClassMethod $parentClassMethod
    ): void {
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
            $classMethod->params = array_merge($cleanParams, $classMethod->params);
        }

        $staticCall = $this->nodeFactory->createParentConstructWithParams($cleanParams);
        $classMethod->stmts[] = new Expression($staticCall);
    }

    /**
     * @param Param[] $params
     * @return Param[]
     */
    private function cleanParamsFromVisibilityAndAttributes(array $params): array
    {
        $cleanParams = $this->promotedPropertyParamCleaner->cleanFromFlags($params);

        // remove deep attributes to avoid bugs with nested tokens re-print
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($cleanParams, function (Node $node): void {
            $node->setAttributes([]);
        });

        return $cleanParams;
    }
}
