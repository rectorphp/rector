<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\Rector\Function_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DowngradePhp72\Rector\Function_\DowngradePregUnmatchedAsNullConstantRector\DowngradePregUnmatchedAsNullConstantRectorTest
 */
final class DowngradePregUnmatchedAsNullConstantRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Function_::class];
    }

    /**
     * @param Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove PREG_UNMATCHED_AS_NULL from preg_match and set null value on empty string matched on each match',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        preg_match('/(a)(b)*(c)/', 'ac', $matches, PREG_UNMATCHED_AS_NULL);
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        preg_match('/(a)(b)*(c)/', 'ac', $matches);
        array_walk_recursive($matches, function (& $value) {
            if ($value === '') {
                $value = null;
            }
        });
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }
}
