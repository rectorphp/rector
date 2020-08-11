<?php

declare(strict_types=1);

namespace App\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class RemoveConstructorDependencyByParentRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string[]
     */
    private array $parentsDependenciesToRemove;

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

        $constructorClassMethod = $node->getMethod('__construct');
        if ($constructorClassMethod === null) {
            return null;
        }

        foreach ($this->parentsDependenciesToRemove as $parentClass => $paramToRemove) {
            $parentClassName = $node->getAttribute(AttributeKey::PARENT_CLASS_NAME);
            if ($parentClassName !== $parentClass) {
                continue;
            }

            $this->removeDependency($constructorClassMethod, $paramToRemove);
        }

        return $node;
    }

    private function removeDependency(
        ClassMethod $classMethod,
        string $paramToRemove
    ): void {
        // remove constructor param
        foreach ($classMethod->params as $key => $param) {
            if ($param->type === null) {
                continue;
            }

            if (! $this->isName($param->type, $paramToRemove)) {
                continue;
            }

            unset($classMethod->params[$key]);
        }
    }

    public function configure(array $configuration): void
    {
        $this->parentsDependenciesToRemove = $configuration['$parentsDependenciesToRemove'] ?? [];
    }
}
