<?php

declare(strict_types=1);

namespace Rector\Generics\Rector\Class_;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://github.com/phpstan/phpstan/issues/3167
 *
 * @see \Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\GenericsPHPStormMethodAnnotationRectorTest
 */
final class GenericsPHPStormMethodAnnotationRector extends AbstractRector
{
    /**
     * @var \Rector\Generics\Reflection\ClassGenericMethodResolver
     */
    private $classGenericMethodResolver;

    public function __construct(\Rector\Generics\Reflection\ClassGenericMethodResolver $classGenericMethodResolver)
    {
        $this->classGenericMethodResolver = $classGenericMethodResolver;
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
            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }

    /**
     * @param \PhpParser\Node\Stmt\Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->extends === null) {
            return null;
        }

        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return null;
        }

        $parentClassReflection = $classReflection->getParentClass();
        if (! $parentClassReflection instanceof ClassReflection) {
            return null;
        }

        if (! $parentClassReflection->isGeneric()) {
            return null;
        }

        // resolve generic method from parent

        $methodTagValueNodes = $this->classGenericMethodResolver->resolveFromClass($parentClassReflection);

        // @todo replace TTypes with specific types
        $templateNames = array_keys($parentClassReflection->getTemplateTags());

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        foreach ($methodTagValueNodes as $methodTagValueNode) {
            $phpDocInfo->addTagValueNode($methodTagValueNode);
        }

        return $node;
    }
}
