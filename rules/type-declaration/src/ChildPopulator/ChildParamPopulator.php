<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\ChildPopulator;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\UnionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\NodeCollector\NodeFinder\ClassLikeParsedNodesFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\SelfObjectType;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\ValueObject\NewType;

final class ChildParamPopulator
{
    /**
     * @var ClassLikeParsedNodesFinder
     */
    private $classLikeParsedNodesFinder;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var RectorChangeCollector
     */
    private $rectorChangeCollector;

    public function __construct(
        ClassLikeParsedNodesFinder $classLikeParsedNodesFinder,
        StaticTypeMapper $staticTypeMapper,
        NodeNameResolver $nodeNameResolver,
        RectorChangeCollector $rectorChangeCollector
    ) {
        $this->classLikeParsedNodesFinder = $classLikeParsedNodesFinder;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->rectorChangeCollector = $rectorChangeCollector;
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

        $childrenClassLikes = $this->classLikeParsedNodesFinder->findClassesAndInterfacesByType($className);

        // update their methods as well
        foreach ($childrenClassLikes as $childClassLike) {
            if ($childClassLike instanceof Class_) {
                $usedTraits = $this->classLikeParsedNodesFinder->findUsedTraitsInClass($childClassLike);

                foreach ($usedTraits as $trait) {
                    $this->addParamTypeToMethod($trait, $position, $functionLike, $paramType);
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
        if ($methodName === null) {
            return;
        }

        $currentClassMethod = $classLike->getMethod($methodName);
        if ($currentClassMethod === null) {
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

        $resolvedChildType = $this->resolveChildTypeNode($paramType);
        if ($resolvedChildType === null) {
            return;
        }

        // let the method know it was changed now
        $paramNode->type = $resolvedChildType;
        $paramNode->type->setAttribute(NewType::HAS_NEW_INHERITED_TYPE, true);

        $this->rectorChangeCollector->notifyNodeFileInfo($paramNode);
    }

    /**
     * @return Name|NullableType|UnionType|null
     */
    private function resolveChildTypeNode(Type $type): ?Node
    {
        if ($type instanceof MixedType) {
            return null;
        }

        if ($type instanceof SelfObjectType || $type instanceof StaticType) {
            $type = new ObjectType($type->getClassName());
        }

        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type);
    }
}
