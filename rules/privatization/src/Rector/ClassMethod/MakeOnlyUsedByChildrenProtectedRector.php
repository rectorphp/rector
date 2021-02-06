<?php

declare(strict_types=1);

namespace Rector\Privatization\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Privatization\Tests\Rector\ClassMethod\MakeOnlyUsedByChildrenProtectedRector\MakeOnlyUsedByChildrenProtectedRectorTest
 */
final class MakeOnlyUsedByChildrenProtectedRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Make public class method protected, if only used by its children',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
abstract class AbstractSomeClass
{
    public function run()
    {
    }
}

class ChildClass extends AbstractSomeClass
{
    public function go()
    {
        $this->run();
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
abstract class AbstractSomeClass
{
    protected function run()
    {
    }
}

class ChildClass extends AbstractSomeClass
{
    public function go()
    {
        $this->run();
    }
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        return $node;
    }
}
