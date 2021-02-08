<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\NodeManipulator\LivingCodeManipulator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\Expression\RemoveDeadStmtRector\RemoveDeadStmtRectorTest
 */
final class RemoveDeadStmtRector extends AbstractRector
{
    /**
     * @var LivingCodeManipulator
     */
    private $livingCodeManipulator;

    public function __construct(LivingCodeManipulator $livingCodeManipulator)
    {
        $this->livingCodeManipulator = $livingCodeManipulator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Removes dead code statements', [
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

    private function removeNodeAndKeepComments(Expression $expression): ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($expression);

        if ($expression->getComments() !== []) {
            $nop = new Nop();
            $nop->setAttribute(AttributeKey::PHP_DOC_INFO, $phpDocInfo);

            $this->phpDocInfoFactory->createFromNode($nop);

            return $nop;
        }

        $this->removeNode($expression);

        return null;
    }
}
