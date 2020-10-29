<?php

declare(strict_types=1);

namespace Rector\Legacy\Rector\Include_;

use PhpParser\Node;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Stmt\Nop;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://github.com/rectorphp/rector/issues/3679
 *
 * @see \Rector\Legacy\Tests\Rector\Include_\RemoveIncludeRector\RemoveIncludeRectorTest
 */
final class RemoveIncludeRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Remove includes (include, include_once, require, require_once) from source', [
                new CodeSample(
                                        <<<'CODE_SAMPLE'
// Comment before require
include 'somefile.php';
// Comment after require
CODE_SAMPLE
                                ,
                                <<<'CODE_SAMPLE'
// Comment before require

// Comment after require
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
        $nop = new Nop();
        $comments = $node->getAttribute(AttributeKey::COMMENTS);
        if ($comments) {
            $nop->setAttribute(AttributeKey::COMMENTS, $comments);
            $this->addNodeAfterNode($nop, $node);
        }
        $this->removeNode($node);

        return $node;
    }
}
