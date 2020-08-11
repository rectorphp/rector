<?php

declare(strict_types=1);

namespace App\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class RemoveParentRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string[]
     */
    private array $parentsToRemove = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes parent extends');
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

        foreach ($this->parentsToRemove as $parentToRemove) {
            $parentClassName = $node->getAttribute(AttributeKey::PARENT_CLASS_NAME);
            if ($parentClassName !== $parentToRemove) {
                continue;
            }

            // remove parent class
            $node->extends = null;
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->parentsToRemove = $configuration['$parentsToRemove'] ?? [];
    }
}
