<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\PostInc;

use PhpParser\Node;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PostDec;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\PostInc\PostIncDecToPreIncDecRector\PostIncDecToPreIncDecRectorTest
 */
final class PostIncDecToPreIncDecRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Use ++$value or --$value  instead of `$value++` or `$value--`',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if ($value++) {
        }

        if ($value--) {
        }
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if (++$value) {
        }

        if (--$value) {
        }
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [PostInc::class, PostDec::class];
    }

    /**
     * @param PostInc|PostDec $node
     */
    public function refactor(Node $node): ?Node
    {
        return null;
    }
}
