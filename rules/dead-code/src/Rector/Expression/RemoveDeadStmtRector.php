<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\DeadCode\Tests\Rector\Expression\RemoveDeadStmtRector\RemoveDeadStmtRectorTest
 */
final class RemoveDeadStmtRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes dead code statements', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$value = 5;
$value;
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$value = 5;
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Expression::class];
    }

    /**
     * @param Expression $node
     */
    public function refactor(Node $node): ?Node
    {
        $livingCode = $this->livingCodeManipulator->keepLivingCodeFromExpr($node->expr);
        if ($livingCode === []) {
            return $this->removeNodeAndKeepComments($node);
        }

        $firstExpr = array_shift($livingCode);
        $node->expr = $firstExpr;

        foreach ($livingCode as $expr) {
            $newNode = new Expression($expr);
            $this->addNodeAfterNode($newNode, $node);
        }

        return null;
    }

    private function removeNodeAndKeepComments(Node $node): ?Node
    {
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

        if ($node->getComments() !== []) {
            $nop = new Nop();
            $nop->setAttribute(AttributeKey::PHP_DOC_INFO, $phpDocInfo);

            $this->phpDocInfoFactory->createFromNode($nop);

            return $nop;
        }

        $this->removeNode($node);

        return null;
    }
}
