<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://3v4l.org/F5GE8
 * @see \Rector\Tests\Php70\Rector\FuncCall\RenameMktimeWithoutArgsToTimeRector\RenameMktimeWithoutArgsToTimeRectorTest
 */
final class RenameMktimeWithoutArgsToTimeRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Renames mktime() without arguments to time()', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $time = mktime(1, 2, 3);
        $nextTime = mktime();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $time = mktime(1, 2, 3);
        $nextTime = time();
    }
}
CODE_SAMPLE
)]);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NO_MKTIME_WITHOUT_ARG;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, 'mktime')) {
            return null;
        }
        if ($node->args !== []) {
            return null;
        }
        $node->name = new Name('time');
        return $node;
    }
}
