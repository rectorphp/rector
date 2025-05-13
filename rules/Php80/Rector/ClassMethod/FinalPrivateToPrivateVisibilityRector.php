<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php80\Rector\ClassMethod\FinalPrivateToPrivateVisibilityRector\FinalPrivateToPrivateVisibilityRectorTest
 */
final class FinalPrivateToPrivateVisibilityRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private VisibilityManipulator $visibilityManipulator;
    public function __construct(VisibilityManipulator $visibilityManipulator)
    {
        $this->visibilityManipulator = $visibilityManipulator;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NO_FINAL_PRIVATE;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change method visibility from final private to only private', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    final private function getter() {
        return $this;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    private function getter() {
        return $this;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $this->visibilityManipulator->makeNonFinal($node);
        return $node;
    }
    private function shouldSkip(ClassMethod $classMethod) : bool
    {
        if (!$classMethod->isFinal()) {
            return \true;
        }
        if ($classMethod->name->toString() === MethodName::CONSTRUCT) {
            return \true;
        }
        return !$classMethod->isPrivate();
    }
}
