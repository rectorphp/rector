<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Php80\Tests\Rector\ClassMethod\FinalPrivateToPrivateVisibilityRector\FinalPrivateToPrivateVisibilityRectorTest
 */
final class FinalPrivateToPrivateVisibilityRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes method visibility from final private to only private', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    final private function getter() {
        return $this;
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    private function getter() {
        return $this;
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
        if ($this->shouldSkip($node)) {
            return null;
        }

        $this->visibilityManipulator->makeNonFinal($node);

        return $node;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        if (! $classMethod->isFinal()) {
            return true;
        }
        return ! $classMethod->isPrivate();
    }
}
