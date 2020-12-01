<?php

declare(strict_types=1);

namespace Rector\Legacy\Rector\Include_;

use PhpParser\Node;
use PhpParser\Node\Expr\Include_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://github.com/rectorphp/rector/issues/3679
 *
 * @see \Rector\Legacy\Tests\Rector\Include_\RemoveIncludeRector\RemoveIncludeRectorTest
 */
final class RemoveIncludeRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove includes (include, include_once, require, require_once) from source', [
                new CodeSample(
                                        <<<'CODE_SAMPLE'
include 'somefile.php';
CODE_SAMPLE
                                ,
                                <<<'CODE_SAMPLE'
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
        return [Include_::class];
    }

    /**
     * @param Include_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->removeNode($node);

        return $node;
    }
}
