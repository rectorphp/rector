<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Stmt\If_;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\If_\CombineIfRector\CombineIfRectorTest
 */
final class CombineIfRector extends AbstractRector
{
    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Merges nested if statements', [
            new CodeSample(
                <<<'PHP'
class SomeClass {
    public function run()
    {
        if ($cond1) {
            if ($cond2) {
                return 'foo';
            }
        }
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass {
    public function run()
    {
        if ($cond1 && $cond2) {
            return 'foo';
        }
    }
}
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }

    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var If_ $subIf */
        $subIf = $node->stmts[0];
        $node->cond = new BooleanAnd($node->cond, $subIf->cond);
        $node->stmts = $subIf->stmts;

        $this->combineComments($node, $subIf);

        return $node;
    }

    private function shouldSkip(If_ $node): bool
    {
        if ($node->else !== null) {
            return true;
        }

        if (count($node->stmts) !== 1) {
            return true;
        }

        if ($node->elseifs !== []) {
            return true;
        }

        if (! $node->stmts[0] instanceof If_) {
            return true;
        }

        if ($node->stmts[0]->else !== null) {
            return true;
        }
        return (bool) $node->stmts[0]->elseifs;
    }

    private function combineComments(Node $firstNode, Node $secondNode): void
    {
        $firstNode->setAttribute('comments', array_merge($firstNode->getComments(), $secondNode->getComments()));

        if ($firstNode->getDocComment() === null) {
            return;
        }

        // update original node php doc info object
        $this->phpDocInfoFactory->createFromNode($firstNode);
    }
}
