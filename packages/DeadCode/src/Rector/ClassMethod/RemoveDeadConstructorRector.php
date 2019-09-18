<?php declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDeadConstructorRector\RemoveDeadConstructorRectorTest
 */
final class RemoveDeadConstructorRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove empty constructor', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function __construct()
    {
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, '__construct')) {
            return null;
        }

        if ($node->stmts === null || count($node->stmts) > 0) {
            return null;
        }

        // Skip private as they lock creating new instances via `new ClassName()`
        if ($node->isPrivate()) {
            return null;
        }

        $this->removeNode($node);

        return null;
    }
}
