<?php

declare(strict_types=1);

namespace Rector\Generics\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Rector\Core\Rector\AbstractRector;
use Rector\Generics\Filter\UnnededMethodTagValueNodeFilter;
use Rector\Generics\NodeType\GenericTypeSpecifier;
use Rector\Generics\Reflection\ClassGenericMethodResolver;
use Rector\Generics\Reflection\GenericClassReflectionAnalyzer;
use Rector\Generics\ValueObject\ChildParentClassReflections;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://github.com/phpstan/phpstan/issues/3167
 *
 * @see \Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector\GenericsPHPStormMethodAnnotationRectorTest
 */
final class GenericsPHPStormMethodAnnotationRector extends AbstractRector
{
    public function __construct(
        private ClassGenericMethodResolver $classGenericMethodResolver,
        private GenericTypeSpecifier $genericTypeSpecifier,
        private GenericClassReflectionAnalyzer $genericClassReflectionAnalyzer,
        private UnnededMethodTagValueNodeFilter $unnededMethodTagValueNodeFilter
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Complete PHPStorm @method annotations, to make it understand the PHPStan/Psalm generics',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
/**
 * @template TEntity as object
 */
abstract class AbstractRepository
{
    /**
     * @return TEntity
     */
    public function find($id)
    {
    }
}

/**
 * @template TEntity as SomeObject
 * @extends AbstractRepository<TEntity>
 */
final class AndroidDeviceRepository extends AbstractRepository
{
}
CODE_SAMPLE

                    ,
                    <<<'CODE_SAMPLE'
/**
 * @template TEntity as object
 */
abstract class AbstractRepository
{
    /**
     * @return TEntity
     */
    public function find($id)
    {
    }
}

/**
 * @template TEntity as SomeObject
 * @extends AbstractRepository<TEntity>
 * @method SomeObject find($id)
 */
final class AndroidDeviceRepository extends AbstractRepository
{
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        $childParentClassReflections = $this->genericClassReflectionAnalyzer->resolveChildParent($node);
        if (! $childParentClassReflections instanceof ChildParentClassReflections) {
            return null;
        }

        // resolve generic method from parent
        $methodTagValueNodes = $this->classGenericMethodResolver->resolveFromClass($childParentClassReflections);

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        $methodTagValueNodes = $this->unnededMethodTagValueNodeFilter->filter(
            $methodTagValueNodes,
            $phpDocInfo,
            $childParentClassReflections,
            $scope
        );

        $this->genericTypeSpecifier->replaceGenericTypesWithSpecificTypes(
            $methodTagValueNodes,
            $node,
            $childParentClassReflections->getChildClassReflection()
        );

        foreach ($methodTagValueNodes as $methodTagValueNode) {
            $phpDocInfo->addTagValueNode($methodTagValueNode);
        }

        return $node;
    }
}
