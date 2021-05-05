<?php

declare(strict_types=1);

namespace Rector\DowngradePhp70\Rector\GroupUse;

use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://wiki.php.net/rfc/group_use_declarations
 *
 * @see \Rector\Tests\DowngradePhp70\Rector\GroupUse\SplitGroupedUseImportsRector\SplitGroupedUseImportsRectorTest
 */
final class SplitGroupedUseImportsRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Refactor grouped use imports to standalone lines', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use SomeNamespace{
    First,
    Second
};
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
use SomeNamespace\First;
use SomeNamespace\Second;
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<\PhpParser\Node>>
     */
    public function getNodeTypes(): array
    {
        return [\PhpParser\Node\Stmt\GroupUse::class];
    }

    /**
     * @param \PhpParser\Node\Stmt\GroupUse $node
     */
    public function refactor(Node $node): ?Node
    {
        // change the node

        return $node;
    }
}
