<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Generic\Tests\Rector\Class_\RemoveParentRector\RemoveParentRectorTest
 */
final class RemoveParentRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const PARENT_TYPES_TO_REMOVE = '$parentsToRemove';

    /**
     * @var string[]
     */
    private $parentClassesToRemove = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes extends class by name', [
            new ConfiguredCodeSample(
                <<<'PHP'
final class SomeClass extends SomeParentClass
{
}
PHP
                ,
                <<<'PHP'
final class SomeClass
{
}
PHP
                , [
                    self::PARENT_TYPES_TO_REMOVE => ['SomeParentClass'],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
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
        if ($node->extends === null) {
            return null;
        }

        foreach ($this->parentClassesToRemove as $parentClassToRemove) {
            $parentClassName = $node->getAttribute(AttributeKey::PARENT_CLASS_NAME);
            if ($parentClassName !== $parentClassToRemove) {
                continue;
            }

            // remove parent class
            $node->extends = null;

            return $node;
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $this->parentClassesToRemove = $configuration[self::PARENT_TYPES_TO_REMOVE] ?? [];
    }
}
