<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\ClassMethod\RemoveEmptyTestMethodRector\RemoveEmptyTestMethodRectorTest
 */
final class RemoveEmptyTestMethodRector extends AbstractPHPUnitRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove empty test methods', [
            new CodeSample(
                <<<'PHP'
class SomeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * testGetTranslatedModelField method
     *
     * @return void
     */
    public function testGetTranslatedModelField()
    {
    }
}
PHP
,
                <<<'PHP'
class SomeTest extends \PHPUnit\Framework\TestCase
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
        if (! $this->isInTestClass($node)) {
            return null;
        }

        if (! $this->isName($node->name, 'test*')) {
            return null;
        }

        if ($node->stmts === null) {
            return null;
        }

        if (count($node->stmts) > 0) {
            return null;
        }

        $this->removeNode($node);

        return null;
    }
}
