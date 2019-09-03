<?php declare(strict_types=1);

namespace Rector\Php\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/F5GE8
 * @see \Rector\Php\Tests\Rector\FuncCall\RenameMktimeWithoutArgsToTimeRector\RenameMktimeWithoutArgsToTimeRectorTest
 */
final class RenameMktimeWithoutArgsToTimeRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $time = mktime(1, 2, 3);
        $nextTime = mktime();
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $time = mktime(1, 2, 3);
        $nextTime = time();
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
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'mktime')) {
            return null;
        }

        if (count($node->args) > 0) {
            return null;
        }

        $node->name = new Name('time');

        return $node;
    }
}
