<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Stmt\If_;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\Comment\CommentsMerger;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\If_\CombineIfRector\CombineIfRectorTest
 */
final class CombineIfRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\Comment\CommentsMerger
     */
    private $commentsMerger;
    public function __construct(\Rector\BetterPhpDocParser\Comment\CommentsMerger $commentsMerger)
    {
        $this->commentsMerger = $commentsMerger;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Merges nested if statements', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if ($cond1) {
            if ($cond2) {
                return 'foo';
            }
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if ($cond1 && $cond2) {
            return 'foo';
        }
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        /** @var If_ $subIf */
        $subIf = $node->stmts[0];
        if ($this->hasVarTag($subIf)) {
            return null;
        }
        $node->cond = new \PhpParser\Node\Expr\BinaryOp\BooleanAnd($node->cond, $subIf->cond);
        $node->stmts = $subIf->stmts;
        $this->commentsMerger->keepComments($node, [$subIf]);
        return $node;
    }
    private function shouldSkip(\PhpParser\Node\Stmt\If_ $if) : bool
    {
        if ($if->else !== null) {
            return \true;
        }
        if (\count($if->stmts) !== 1) {
            return \true;
        }
        if ($if->elseifs !== []) {
            return \true;
        }
        if (!$if->stmts[0] instanceof \PhpParser\Node\Stmt\If_) {
            return \true;
        }
        if ($if->stmts[0]->else !== null) {
            return \true;
        }
        return (bool) $if->stmts[0]->elseifs;
    }
    private function hasVarTag(\PhpParser\Node\Stmt\If_ $if) : bool
    {
        $subIfPhpDocInfo = $this->phpDocInfoFactory->createFromNode($if);
        if (!$subIfPhpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
            return \false;
        }
        return $subIfPhpDocInfo->getVarTagValueNode() instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
    }
}
