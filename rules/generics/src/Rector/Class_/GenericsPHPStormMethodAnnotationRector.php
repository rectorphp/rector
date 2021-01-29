<?php

declare(strict_types=1);

namespace Rector\Generics\Rector\Class_;

use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://github.com/phpstan/phpstan/issues/3167
 *
 * @see \Rector\Generics\Tests\Rector\Class_\GenericsPHPStormMethodAnnotationRector\GenericsPHPStormMethodAnnotationRectorTest
 */
final class GenericsPHPStormMethodAnnotationRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Complete PHPStorm @method annotations, to make it understand the PHPStan/Psalm generics', [
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

            )
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
        // change the node

        return $node;
    }
}
