<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\UseUse;
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

        return $class->stmts !== [];
    }

    private function processRemove(Class_ $class): ?Class_
    {
        $className = $this->getName($class->namespacedName);
        $names = $this->nodeRepository->findNames($className);

        foreach ($names as $name) {
            $parent = $name->getAttribute(AttributeKey::PARENT_NODE);
            if ($parent instanceof Class_) {
                continue;
            }
            if ($parent instanceof UseUse) {
                continue;
            }
            return null;
        }

        $children = $this->nodeRepository->findChildrenOfClass($className);

        foreach ($children as $child) {
            if ($class->extends !== null) {
                $parentClass = $this->getName($class->extends);
                $child->extends = new FullyQualified($parentClass);
            } else {
                $child->extends = null;
            }
        }

        $this->removeNode($class);

        return $class;
    }
}
