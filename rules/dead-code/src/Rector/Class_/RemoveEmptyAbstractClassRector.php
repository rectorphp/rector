<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\PhpParser\Node\CustomNode\FileNode;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector\RemoveEmptyAbstractClassRectorTest
 */
final class RemoveEmptyAbstractClassRector extends AbstractRector
{
    /**
     * @var FullyQualified[]
     */
    private $fullyQualifieds = [];

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FileNode::class, Class_::class];
    }

    /**
     * @param FileNode|Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof FileNode) {
            $fullyQualifieds = $this->betterNodeFinder->findInstanceOf($node, FullyQualified::class);
            foreach ($fullyQualifieds as $fullyQualified) {
                $parent = $fullyQualified->getAttribute(AttributeKey::PARENT_NODE);
                if ($parent instanceof Class_) {
                    continue;
                }

                $this->fullyQualifieds[] = $fullyQualified;
            }
            return $node;
        }

        if ($this->shouldSkip($node)) {
            return null;
        }

        return $this->processRemove($node);
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Empty abstract class that does nothing',
            [
                new CodeSample(
<<<'CODE_SAMPLE'
class SomeClass extends SomeAbstractClass
{
}

abstract class SomeAbstractClass extends AnotherAbstractClass
{
}

abstract class AnotherAbstractClass
{
     public function getName()
     {
        return 'name';
     }
}
CODE_SAMPLE
,
<<<'CODE_SAMPLE'
class SomeClass extends AnotherAbstractClass
{
}

abstracst clas AnotherAbstractClass
{
     public function getName()
     {
          return 'cowo';
     }
}
CODE_SAMPLE
                ),

            ]);
    }

    private function shouldSkip(Class_ $class): bool
    {
        if (! $class->isAbstract()) {
            return true;
        }

        if ($class->implements !== []) {
            return true;
        }

        $stmts = $class->stmts;
        return $stmts !== [];
    }

    private function processRemove(Class_ $class): ?Class_
    {
        if (! $class->extends instanceof FullyQualified) {
            return null;
        }

        $className = $class->namespacedName->toString();
        foreach ($this->fullyQualifieds as $fullyQualified) {
            if ($className === $fullyQualified->toString()) {
                return null;
            }
        }

        $children = $this->nodeRepository->findChildrenOfClass($this->getName($class->namespacedName));
        $extends = $class->extends;
        foreach ($children as $child) {
            $child->extends = $extends;
        }

        $this->removeNode($class);
        return $class;
    }
}
