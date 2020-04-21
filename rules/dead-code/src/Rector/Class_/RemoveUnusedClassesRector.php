<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Caching\Contract\Rector\ZeroCacheRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DeadCode\UnusedNodeResolver\UnusedClassResolver;

/**
 * @see \Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedClassesRector\RemoveUnusedClassesRectorTest
 */
final class RemoveUnusedClassesRector extends AbstractRector implements ZeroCacheRectorInterface
{
    /**
     * @var UnusedClassResolver
     */
    private $unusedClassResolver;

    public function __construct(UnusedClassResolver $unusedClassResolver)
    {
        $this->unusedClassResolver = $unusedClassResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove unused classes without interface', [
            new CodeSample(
                <<<'PHP'
interface SomeInterface
{
}

class SomeClass implements SomeInterface
{
    public function run($items)
    {
        return null;
    }
}

class NowhereUsedClass
{
}
PHP
,
                <<<'PHP'
interface SomeInterface
{
}

class SomeClass implements SomeInterface
{
    public function run($items)
    {
        return null;
    }
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
        if ($this->shouldSkip($node)) {
            return null;
        }

        if ($this->unusedClassResolver->isClassUsed($node)) {
            return null;
        }

        $this->removeNode($node);

        return null;
    }

    private function shouldSkip(Class_ $class): bool
    {
        if (! $this->unusedClassResolver->isClassWithoutInterfaceAndNotController($class)) {
            return true;
        }

        if ($this->isDoctrineEntityClass($class)) {
            return true;
        }

        // most of factories can be only registered in config and create services there
        // skip them for now; but in the future, detect types they create in public methods and only keep them, if they're used
        if ($this->isName($class, '*Factory')) {
            return true;
        }

        return $class->isAbstract();
    }
}
