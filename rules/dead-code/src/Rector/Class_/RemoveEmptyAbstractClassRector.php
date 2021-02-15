<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use ReflectionClass;

/**
 * @see \Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector\RemoveEmptyAbstractClassRectorTest
 */
final class RemoveEmptyAbstractClassRector extends AbstractRector
{
    /**
     * @var FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;

    /**
     * @var string[]
     */
    private $removedAbstractClasses = [];

    public function __construct(FamilyRelationsAnalyzer $familyRelationsAnalyzer)
    {
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
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
        if ($this->shouldSkip($node)) {
            return null;
        }

        $this->removedAbstractClasses[] = $this->getName($node->namespacedName);
        return $this->processRemove($node);
    }

    private function shouldSkip(Class_ $class): bool
    {
        if (! $class->isAbstract()) {
            return false;
        }

        if ($class->implements !== []) {
            return false;
        }

        $stmts = $class->stmts;
        return $stmts !== [];
    }

    private function processRemove(Class_ $class): ?Class_
    {
        if (! $class->extends instanceof FullyQualified) {
            return null;
        }

        $extends  = $class->extends;
        $children = $this->familyRelationsAnalyzer->getChildrenOfClass($this->getName($class->namespacedName));

        foreach ($children as $child) {
            if (in_array($child->extends->toString(), $this->removedAbstractClasses, true)) {
                continue;
            }

            $child->extends = $extends;
        }

        $this->removeNode($class);
        return $class;
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
}
