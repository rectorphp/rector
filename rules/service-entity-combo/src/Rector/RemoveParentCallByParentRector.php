<?php

declare(strict_types=1);

namespace App\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\PhpParser\Node\Manipulator\ClassMethodManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class RemoveParentCallByParentRector extends AbstractRector implements ConfigurableRectorInterface
{
    private ClassMethodManipulator $classMethodManipulator;
    /** @var string[] */
    private array $parentClasses;

    public function __construct(
        ClassMethodManipulator $classMethodManipulator
    )
    {
        $this->classMethodManipulator = $classMethodManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove parent call by parent class', []);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $class = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class instanceof Class_) {
            return null;
        }

        if (! $node->class instanceof Name) {
            return null;
        }

        if (! $this->isName($node->class, 'parent')) {
            return null;
        }

        $parentClassName = $node->getAttribute(AttributeKey::PARENT_CLASS_NAME);

        foreach ($this->parentClasses as $parentClass) {
            if ($parentClassName === $parentClass) {
                $this->removeNode($node);
                return null;
            }
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->parentClasses = $configuration['$parentClasses'] ?? [];
    }
}
