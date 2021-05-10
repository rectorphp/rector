<?php

declare (strict_types=1);
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
 * @see \Rector\Tests\DeadCode\Rector\Expression\RemoveDeadStmtRector\RemoveDeadStmtRectorTest
 */
final class RemoveDeadStmtRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\DeadCode\NodeManipulator\LivingCodeManipulator
     */
    private $livingCodeManipulator;
    public function __construct(\Rector\DeadCode\NodeManipulator\LivingCodeManipulator $livingCodeManipulator)
    {
        $this->livingCodeManipulator = $livingCodeManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Removes dead code statements', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$value = 5;
$value;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$value = 5;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Expression::class];
    }
    /**
     * @param Expression $node
     * @return Node[]|Node|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        $livingCode = $this->livingCodeManipulator->keepLivingCodeFromExpr($node->expr);
        if ($livingCode === []) {
            return $this->removeNodeAndKeepComments($node);
        }
        $firstExpr = \array_shift($livingCode);
        $node->expr = $firstExpr;
        $newNodes = [];
        foreach ($livingCode as $singleLivingCode) {
            $newNodes[] = new \PhpParser\Node\Stmt\Expression($singleLivingCode);
        }
        $newNodes[] = $node;
        return $newNodes;
    }
    private function removeNodeAndKeepComments(\PhpParser\Node\Stmt\Expression $expression) : ?\PhpParser\Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($expression);
        if ($expression->getComments() !== []) {
            $nop = new \PhpParser\Node\Stmt\Nop();
            $nop->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO, $phpDocInfo);
            $this->phpDocInfoFactory->createFromNode($nop);
            return $nop;
        }
        $this->removeNode($expression);
        return null;
    }
}
