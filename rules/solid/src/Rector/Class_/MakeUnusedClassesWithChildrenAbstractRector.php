<?php

declare(strict_types=1);

namespace Rector\SOLID\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\SOLID\Tests\Rector\Class_\MakeUnusedClassesWithChildrenAbstractRector\MakeUnusedClassesWithChildrenAbstractRectorTest
 */
final class MakeUnusedClassesWithChildrenAbstractRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Classes that have no children nor are used, should have abstract', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass extends PossibleAbstractClass
{
}

class PossibleAbstractClass
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass extends PossibleAbstractClass
{
}

abstract class PossibleAbstractClass
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
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $className = $this->getName($node);
        if ($className === null) {
            return null;
        }
        $nodeRepositoryFindMethodCallsOnClass = $this->nodeRepository->findMethodCallsOnClass($className);

        // 1. is in static call?
        if ($nodeRepositoryFindMethodCallsOnClass !== []) {
            return null;
        }
        $parsedNodeCollectorFindNewsByClass = $this->parsedNodeCollector->findNewsByClass($className);

        // 2. is in new?
        if ($parsedNodeCollectorFindNewsByClass !== []) {
            return null;
        }
        $nodeRepositoryFindChildrenOfClass = $this->nodeRepository->findChildrenOfClass($className);

        // 3. does it have any children
        if ($nodeRepositoryFindChildrenOfClass === []) {
            return null;
        }

        // is abstract!
        if ($node->isAbstract()) {
            return null;
        }

        $this->makeAbstract($node);

        return $node;
    }
}
