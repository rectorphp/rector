<?php

declare (strict_types=1);
namespace Rector\Privatization\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This was deprecated, as its functionality caused bugs. Without knowing the full dependency tree, its very risky to use. Use https://github.com/TomasVotruba/finalize instead as it runs with full context.
 */
final class FinalizeClassesWithoutChildrenRector extends AbstractRector
{
    /**
     * @var bool
     */
    private $hasWarned = \false;
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Finalize every class that has no children', [new CodeSample(<<<'CODE_SAMPLE'
class FirstClass extends SecondClass
{
}

class SecondClass
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class FirstClass extends SecondClass
{
}

class SecondClass
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->hasWarned) {
            return null;
        }
        \trigger_error(\sprintf('The "%s" rule was deprecated, as its functionality caused bugs. Without knowing the full dependency tree, its risky to change.', self::class));
        \sleep(3);
        $this->hasWarned = \true;
        return null;
    }
}
