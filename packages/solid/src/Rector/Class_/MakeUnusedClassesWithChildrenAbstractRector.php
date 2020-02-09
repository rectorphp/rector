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
                <<<'PHP'
class SomeClass extends PossibleAbstractClass
{
}

class PossibleAbstractClass
{
}
PHP
                ,
                <<<'PHP'
class SomeClass extends PossibleAbstractClass
{
}

abstract class PossibleAbstractClass
{
}
PHP
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

        // 1. is in static call?
        if ($this->functionLikeParsedNodesFinder->findMethodCallsOnClass($className) !== []) {
            return null;
        }

        // 2. is in new?
        if ($this->parsedNodeCollector->findNewNodesByClass($className) !== []) {
            return null;
        }

        // 3. does it have any children
        if ($this->classLikeParsedNodesFinder->findChildrenOfClass($className) === []) {
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
