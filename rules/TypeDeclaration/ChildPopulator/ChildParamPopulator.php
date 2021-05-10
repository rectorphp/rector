<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\ChildPopulator;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\Type;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\NodeTypeAnalyzer\ChildTypeResolver;
use Rector\TypeDeclaration\ValueObject\NewType;

final class ChildParamPopulator
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private RectorChangeCollector $rectorChangeCollector,
        private NodeRepository $nodeRepository,
        private ChildTypeResolver $childTypeResolver
    ) {
    }

    /**
     * Add typehint to all children
     * @param ClassMethod|Function_ $functionLike
     */
    public function populateChildClassMethod(FunctionLike $functionLike, int $position, Type $paramType): void
    {
        if (! $functionLike instanceof ClassMethod) {
            return;
        }

        /** @var string|null $className */
        $className = $functionLike->getAttribute(AttributeKey::CLASS_NAME);
        // anonymous class
        if ($className === null) {
            return;
        }

        $childrenClassLikes = $this->nodeRepository->findClassesAndInterfacesByType($className);

        // update their methods as well
        foreach ($childrenClassLikes as $childClassLike) {
            if ($childClassLike instanceof Class_) {
                $usedTraits = $this->nodeRepository->findUsedTraitsInClass($childClassLike);

                foreach ($usedTraits as $usedTrait) {
                    $this->addParamTypeToMethod($usedTrait, $position, $functionLike, $paramType);
                }
            }

            $this->addParamTypeToMethod($childClassLike, $position, $functionLike, $paramType);
        }
    }

    private function addParamTypeToMethod(
        ClassLike $classLike,
        int $position,
        ClassMethod $classMethod,
        Type $paramType
    ): void {
        $methodName = $this->nodeNameResolver->getName($classMethod);

        $currentClassMethod = $classLike->getMethod($methodName);
        if (! $currentClassMethod instanceof ClassMethod) {
            return;
        }

        if (! isset($currentClassMethod->params[$position])) {
            return;
        }

        $paramNode = $currentClassMethod->params[$position];

        // already has a type
        if ($paramNode->type !== null) {
            return;
        }

        $resolvedChildType = $this->childTypeResolver->resolveChildTypeNode($paramType);
        if ($resolvedChildType === null) {
            return;
        }

        // let the method know it was changed now
        $paramNode->type = $resolvedChildType;
        $paramNode->type->setAttribute(NewType::HAS_NEW_INHERITED_TYPE, true);

        $this->rectorChangeCollector->notifyNodeFileInfo($paramNode);
    }
}
