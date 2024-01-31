<?php

declare (strict_types=1);
namespace Rector\Php81\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This was deprecated, as its functionality caused bugs. Without knowing the full dependency tree, its risky to change
 */
final class FinalizePublicClassConstantRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @var bool
     */
    private $hasWarned = \false;
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add final to constants that does not have children', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public const NAME = 'value';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    final public const NAME = 'value';
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
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::FINAL_CLASS_CONSTANTS;
    }
}
